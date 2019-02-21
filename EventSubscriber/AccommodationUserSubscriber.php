<?php /** @noinspection ForgottenDebugOutputInspection */

namespace ZakJakub\OswisAccommodationBundle\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zakjakub\OswisAccommodationBundle\Entity\JobFairUser;
use Zakjakub\OswisAccommodationBundle\Entity\JobFairUserCategory;
use Zakjakub\OswisCoreBundle\Entity\AppUser;
use Zakjakub\OswisCoreBundle\Entity\AppUserType;
use Zakjakub\OswisCoreBundle\Manager\AppUserManager;

final class AccommodationUserSubscriber implements EventSubscriberInterface
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
            KernelEvents::VIEW => ['accommodationUserSubscriber', EventPriorities::PRE_WRITE],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \Exception
     */
    public function accommodationUserSubscriber(GetResponseForControllerResultEvent $event): void
    {
        // \error_log('makeReservation().');
        $accommodationUser = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$accommodationUser instanceof JobFairUser || (Request::METHOD_POST !== $method && Request::METHOD_PUT !== $method)) {
            return;
        }

        $this->logger->info('AccommodationUserSubscriber function();');

        $accommodationUserRepository = $this->em->getRepository(JobFairUser::class);
        $currentAccommodationUser = $this->getAccommodationUser();
        $currentAppUser = $currentAccommodationUser ? $currentAccommodationUser->getAppUser() : null;

        $this->logger->info(\serialize($accommodationUser->getAppUser()));

        if (Request::METHOD_PUT === $method) {
            $this->logger->info('AccommodationUserSubscriber PUT;');
            if (!$currentAppUser) {
                throw new AccessDeniedException('Uživatel nepřihlášen.');
            }
            if ($currentAppUser !== $accommodationUser->getAppUser()
                && !$currentAppUser->containsRole('ROLE_MANAGER')) {
                throw new AccessDeniedException('Chybí potřebná oprávnění.');
            }
            if (!$accommodationUser || !$accommodationUser->getAppUser()) {
                throw new \ErrorException('Neznámý uživatel.');
            }
            $oldAccommodationUser = $accommodationUserRepository->findOneBy(['id' => $accommodationUser->getAppUser()->getId()]);
            \assert($oldAccommodationUser instanceof JobFairUser);
            if (!$oldAccommodationUser->getAppUser()) {
                throw new \ErrorException('Chybí uživatel u ubytovacího účtu.');
            }
            if (!$currentAppUser->containsRole('ROLE_MANAGER')) {
                if ($oldAccommodationUser->getDeleted() !== $accommodationUser->getDeleted()
                    || $oldAccommodationUser->getAppUser()->getId() !== $accommodationUser->getAppUser()->getId()
                    || $oldAccommodationUser->getAppUser()->getDeleted() !== $accommodationUser->getAppUser()->getDeleted()
                    || $oldAccommodationUser->getAccommodationUserCategory()->getId() !== $accommodationUser->getAccommodationUserCategory()->getId()
                    || $oldAccommodationUser->getAppUser()->getAppUserType()->getId() !== $accommodationUser->getAppUser()->getAppUserType()->getId()
                ) {
                    throw new NotFoundHttpException('Nedostatečná oprávnění ke změně některých parametrů.');
                }
            }
        }

        /// TODO: Edit serialization groups (appUserId only in put, not in post??).

        if (Request::METHOD_POST === $method) {
            $this->logger->info('AccommodationUserSubscriber POST;');
            if (!$accommodationUser->getAppUser()) {
                throw new \InvalidArgumentException('V požadavku chybí uživatel.');
            }
            if (!$currentAppUser || !$currentAppUser->containsRole('ROLE_MANAGER')) {
                $appUserTypeRepository = $this->em->getRepository(AppUserType::class);
                $basicUserType = $appUserTypeRepository->findOneBy(['shortName' => 'customer']);
                if (!$basicUserType) {
                    throw new \ErrorException('Typ uživatele "customer" nebyl nalezen.');
                }
                $accommodationUser->getAppUser()->setAppUserType($basicUserType);
            }
            $appUserManager = new AppUserManager($this->encoder, $this->em, $this->mailer, $this->logger, $this->templating);
            $appUserManager->requestUserActivation($accommodationUser->getAppUser());
        }

        /// TODO: Some things.

    }

    /**
     * @return JobFairUser
     * @throws \Exception
     */
    public function getAccommodationUser(): ?JobFairUser
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

}
