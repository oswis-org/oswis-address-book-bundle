<?php /** @noinspection ForgottenDebugOutputInspection */

namespace ZakJakub\OswisAccommodationBundle\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Exception\FeatureNotImplementedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zakjakub\OswisAccommodation\Manager\ReservationPaymentManager;
use Zakjakub\OswisAccommodationBundle\Entity\JobFairUser;
use Zakjakub\OswisAccommodationBundle\Entity\JobFairUserCategory;
use Zakjakub\OswisAccommodationBundle\Entity\ReservationPayment;
use Zakjakub\OswisCoreBundle\Entity\AppUser;

final class ReservationPaymentSubscriber implements EventSubscriberInterface
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
            KernelEvents::VIEW => [
                ['makeReservationPayment', EventPriorities::PRE_WRITE],
                ['sendEmail', EventPriorities::POST_WRITE],
            ],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \Exception
     */
    public function sendEmail(GetResponseForControllerResultEvent $event): void
    {
        $payment = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$payment instanceof ReservationPayment || Request::METHOD_POST !== $method) {
            return;
        }
        // \error_log('makeReservationPayment()->sendMail()');
        $reservationPaymentManager = new ReservationPaymentManager($this->em, $this->mailer, $this->logger, $this->templating);
        $reservationPaymentManager->sendReceiptPdf($payment);
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \Exception
     */
    public function makeReservationPayment(GetResponseForControllerResultEvent $event): void
    {
        $payment = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$payment instanceof ReservationPayment || (Request::METHOD_POST !== $method && Request::METHOD_PUT !== $method)) {
            return;
        }

        if (Request::METHOD_PUT === $method) {
            throw new FeatureNotImplementedException('Úprava plateb není implementována.');
        }
        // \error_log('makeReservationPayment()');
        $payment->setAuthor($this->getAccommodationUser());

        // $reservationPaymentManager = new ReservationPaymentManager($this->em, $this->mailer, $this->logger, $this->templating);
        // $reservationPaymentManager->sendReceiptPdf($payment);
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
            throw new AccessDeniedException('Neznámý uživatel.');
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

}
