<?php

namespace Zakjakub\OswisAccommodationBundle\Api\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zakjakub\OswisAccommodationBundle\Entity\PersonStay;

final class RoomsOccupancyActionSubscriber implements EventSubscriberInterface
{

    private $personStayRepository;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->personStayRepository = $em->getRepository(PersonStay::class);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['roomsOccupancyAction', EventPriorities::POST_VALIDATE],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \Exception
     */
    public function roomsOccupancyAction(GetResponseForControllerResultEvent $event): void
    {
        $request = $event->getRequest();
        if ('api_rooms_occupancy_action_requests_post_collection' !== $request->attributes->get('_route')) {
            return;
        }
        $controllerResult = $event->getControllerResult();
        $startDateTime = $controllerResult->startDateTime;
        $endDateTime = $controllerResult->endDateTime;
        $output = $this->personStayRepository->getRoomsOccupancy($startDateTime, $endDateTime);

        foreach ($output as &$item) {
            $item['start'] = $item['start']->setTime(14, 0)->format('Y-m-d H:i:s');
            $item['end'] = $item['end']->setTime(11, 0)->format('Y-m-d H:i:s');
        }

        $event->setResponse(new JsonResponse($output, Response::HTTP_OK));
    }


}

