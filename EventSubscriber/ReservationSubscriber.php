<?php /** @noinspection ForgottenDebugOutputInspection */

namespace ZakJakub\OswisAccommodationBundle\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Zakjakub\OswisAccommodationBundle\Entity\JobFairUser;
use Zakjakub\OswisAccommodationBundle\Entity\JobFairUserCategory;
use Zakjakub\OswisAccommodationBundle\Entity\Reservation;
use Zakjakub\OswisAccommodationBundle\Entity\Room;
use Zakjakub\OswisCoreBundle\Entity\AppUser;

/**
 * Class ReservationSubscriber
 * @package ZakJakub\OswisAccommodationBundle\EventSubscriber
 */
final class ReservationSubscriber implements EventSubscriberInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Twig_Environment
     */
    private $templating;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * ReservationSubscriber constructor.
     *
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface       $em
     * @param \Swift_Mailer                $mailer
     * @param LoggerInterface              $logger
     * @param \Twig_Environment            $templating
     * @param TokenStorageInterface        $tokenStorage
     */
    public function __construct(
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $em,
        \Swift_Mailer $mailer,
        LoggerInterface $logger,
        \Twig_Environment $templating,
        TokenStorageInterface $tokenStorage
    ) {
        // \error_log('Constructing ReservationSubscriber.');
        $this->encoder = $encoder;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['makeReservation', EventPriorities::PRE_WRITE],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \Zakjakub\OswisCoreBundle\Exceptions\RevisionMissingException
     * @throws \Exception
     */
    public function makeReservation(GetResponseForControllerResultEvent $event): void
    {
        // \error_log('makeReservation().');
        $reservation = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$reservation instanceof Reservation || (Request::METHOD_POST !== $method && Request::METHOD_PUT !== $method)) {
            return;
        }

        // \error_log('Start personStays.');
        $roomRepo = $this->em->getRepository(Room::class);
        foreach ($reservation->getPersonStays() as $personStay) {
            \assert($personStay instanceof PersonStay);
            if (!$personStay->getRoom()) {
                throw new \Exception('Nebyl vybrán pokoj.');
            }
            $room = $roomRepo->findOneBy(['id' => $personStay->getRoom()->getId()]);
            \assert($room instanceof Room);
            $personStay->setRoom($room);
            if (!$personStay->getPerson()) {
                throw new \Exception('Nebyla vybrána osoba.');
            }
            // \error_log(
            //    'PersonStay: room '.($personStay->getRoom() ? $personStay->getRoom()->getId().' - '.$personStay->getRoom()->getName(
            //        ) : null).', person'.($personStay->getPerson() ? $personStay->getPerson()->getFullName() : null).'.'
            // );
        }
        // \error_log('End personStays.');


        $accommodationUser = $this->getAccommodationUser();
        $appUser = $accommodationUser->getAppUser();
        // \error_log('Inside makeReservation(). User: '.$accommodationUser->getUsername());
        \assert($reservation instanceof Reservation);
        $roles = new ArrayCollection($appUser ? $appUser->getRoles() : null);
        if (!$reservation->getCustomer() || !$roles->contains('ROLE_MANAGER')) {
            $reservation->setCustomer($accommodationUser);
        }

        $reservation->calculateDepositValue();

        /// TODO: Check number of persons in rooms and availability.
    }

    /**
     * @return JobFairUser
     * @throws \Exception
     */
    public function getAccommodationUser(): JobFairUser
    {
        // maybe these extra null checks are not even needed
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }
        $appUser = $token->getUser();
        if (!$appUser instanceof AppUser) {
            return null;
        }
        if (!$appUser) {
            throw new \Exception('Neznámý uživatel.');
        }
        $accommodationUserRepo = $this->em->getRepository(JobFairUser::class);
        $accommodationUser = $accommodationUserRepo->findOneBy(['appUser' => $appUser->getId()]);
        if (!$accommodationUser) {
            $accommodationUserCategoryRepo = $this->em->getRepository(JobFairUserCategory::class);
            $accommodationUserCategory = $accommodationUserCategoryRepo->findOneBy(['short_name' => 'ostatni']);
            \assert($accommodationUserCategory instanceof JobFairUserCategory);
            $accommodationUser = new JobFairUser($accommodationUserCategory, $appUser);
            $this->em->persist($accommodationUser);
            $this->em->flush();
        }
        \assert($accommodationUser instanceof JobFairUser);

        return $accommodationUser;
    }

    /**
     * @param JobFairUser $accommodationUser
     * @param Reservation $reservation
     *
     * @return bool
     * @throws \Zakjakub\OswisCoreBundle\Exceptions\RevisionMissingException
     * @throws \Exception
     */
    public function canUserModifyReservation(JobFairUser $accommodationUser, Reservation $reservation): bool
    {
        $appUser = $accommodationUser->getAppUser();
        if ($appUser && $appUser->containsRole('ROLE_MANAGER')) {
            return true;
        }
        if ($reservation->getCustomerEditLocked()) {
            throw new \Exception('Rezervace je uzamčena pro úpravy.');
        }
        if ($reservation->getCustomer() === $accommodationUser) {
            return true;
        }
        $allowedStartDateTime = $accommodationUser->getAccommodationUserCategory()
            && $accommodationUser->getAccommodationUserCategory()->getStartDateTime();
        $allowedEndDateTime = $accommodationUser->getAccommodationUserCategory()
            && $accommodationUser->getAccommodationUserCategory()->getEndDateTime();
        $reservationStartDateTime = $reservation->getStartDateTime();
        $reservationEndDateTime = $reservation->getEndDateTime();

        if (($allowedStartDateTime && $reservationStartDateTime < $allowedStartDateTime)
            || ($reservationEndDateTime > $allowedEndDateTime && $allowedEndDateTime)) {
            // TODO: Return allowed period.
            throw new \Exception('Uživatel nemá povoleny rezervace v tomto termínu.');
        }

        if ($reservation->getId()) {
            throw new \Exception('Úprava rezervace nebyla povolena.');
        }
        throw new \Exception('Nepodařilo se vytvořit rezervaci.');
    }

}
