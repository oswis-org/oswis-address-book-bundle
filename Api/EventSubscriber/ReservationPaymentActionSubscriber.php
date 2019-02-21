<?php

namespace Zakjakub\OswisAccommodationBundle\Api\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zakjakub\OswisAccommodation\Manager\ReservationPaymentManager;
use Zakjakub\OswisAccommodationBundle\Entity\ReservationPayment;

final class ReservationPaymentActionSubscriber implements EventSubscriberInterface
{

    public const ALLOWED_ACTION_TYPES = ['get-receipt-pdf', 'send-receipt-customer'];

    /**
     * @var EntityRepository
     */
    private $reservationPaymentRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ReservationPaymentManager
     */
    private $reservationPaymentManager;

    public function __construct(
        EntityManagerInterface $em,
        \Swift_Mailer $mailer,
        LoggerInterface $logger,
        \Twig_Environment $templating
    ) {
        $this->em = $em;
        $this->reservationPaymentManager = new ReservationPaymentManager($em, $mailer, $logger, $templating);
        $this->reservationPaymentRepository = $em->getRepository(ReservationPayment::class);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        \error_log('GET SUBSCRIBED');

        return [
            KernelEvents::VIEW => ['reservationPaymentAction', EventPriorities::POST_VALIDATE],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \Exception
     */
    public function reservationPaymentAction(GetResponseForControllerResultEvent $event): void
    {

        \error_log('IN FUNCTION');

        $request = $event->getRequest();

        if ('api_reservation_payment_action_requests_post_collection' !== $request->attributes->get('_route')) {
            return;
        }

        \error_log('MY FUNCTION');

        $output = null;

        $reservationPaymentActionRequest = $event->getControllerResult();

        $identifiers = $reservationPaymentActionRequest->identifiers;
        $type = $reservationPaymentActionRequest->type;

        if (!\in_array($type, self::ALLOWED_ACTION_TYPES)) {
            $event->setResponse(new JsonResponse(null, Response::HTTP_NOT_IMPLEMENTED));

            return;
        }

        $processedActionsCount = 0;
        $reservations = new ArrayCollection();
        foreach ($identifiers as $id) {
            $payment = $this->reservationPaymentRepository->findOneBy(['id' => $id]);
            if (!$payment) {
                continue;
            }
            \assert($payment instanceof ReservationPayment);
            $reservations->add($payment);
            switch ($type) {
                case 'get-receipt-pdf':
                    $output = $this->reservationPaymentManager->createReceiptPdfString($payment);
                    $processedActionsCount++;
                    break;
                case 'send-receipt-pdf-customer':
                    $this->reservationPaymentManager->sendReceiptPdf($payment);
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
