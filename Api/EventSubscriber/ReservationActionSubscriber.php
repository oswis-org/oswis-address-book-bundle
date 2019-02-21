<?php

namespace Zakjakub\OswisAccommodationBundle\Api\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Zakjakub\OswisAccommodation\Manager\ReservationManager;
use Zakjakub\OswisAccommodationBundle\Entity\Reservation;
use Zakjakub\OswisAccommodationBundle\Entity\ReservationPayment;

final class ReservationActionSubscriber implements EventSubscriberInterface
{

    public const ALLOWED_ACTION_TYPES = ['get-decree-pdf', 'send-decree-customer'];

    private $em;

    private $mailer;

    private $logger;

    private $templating;

    private $reservationManager;

    private $reservationRepository;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $em,
        \Swift_Mailer $mailer,
        LoggerInterface $logger,
        \Twig_Environment $templating,
        TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->reservationRepository = $em->getRepository(Reservation::class);
        $this->reservationManager = new ReservationManager($em, $mailer, $logger, $templating, $tokenStorage);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['reservationAction', EventPriorities::POST_VALIDATE],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \Exception
     */
    public function reservationAction(GetResponseForControllerResultEvent $event): void
    {

        $request = $event->getRequest();

        if ('api_reservation_action_requests_post_collection' !== $request->attributes->get('_route')) {
            return;
        }

        $output = null;

        $reservationPaymentActionRequest = $event->getControllerResult();

        $identifiers = $reservationPaymentActionRequest->identifiers;
        $type = $reservationPaymentActionRequest->type;

        if (!\in_array($type, self::ALLOWED_ACTION_TYPES, true)) {
            $event->setResponse(new JsonResponse(null, Response::HTTP_NOT_IMPLEMENTED));

            return;
        }

        $processedActionsCount = 0;
        $reservations = new ArrayCollection();
        foreach ($identifiers as $id) {
            $reservation = $this->reservationRepository->findOneBy(['id' => $id]);
            if (!$reservation) {
                continue;
            }
            \assert($reservation instanceof ReservationPayment);
            $reservations->add($reservation);
            switch ($type) {
                case 'get-decree-pdf':
                    $output = $this->reservationManager->createDecreePdfString($reservation);
                    $processedActionsCount++;
                    break;
                case 'send-decree-pdf-customer':
                    $this->reservationManager->sendDecreePdf($reservation);
                    $processedActionsCount++;
                    break;
                default:
                    $event->setResponse(new JsonResponse(null, Response::HTTP_NOT_IMPLEMENTED));

                    return;
                    break;
            }
        }

        if ($processedActionsCount === 0) {
            $event->setResponse(new JsonResponse(null, Response::HTTP_NOT_FOUND));

            return;
        }

        if ($output) {
            $data = ['data' => chunk_split(base64_encode($output))];
            $event->setResponse(new JsonResponse($data, Response::HTTP_CREATED));

            return;
        }

        $event->setResponse(new JsonResponse(null, Response::HTTP_NO_CONTENT));
    }


}
