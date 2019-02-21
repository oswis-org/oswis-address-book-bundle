<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Mpdf\Mpdf;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Zakjakub\OswisAccommodationBundle\Entity\JobFairUser;
use Zakjakub\OswisAccommodationBundle\Entity\PersonStay;
use Zakjakub\OswisAccommodationBundle\Entity\Reservation;
use Zakjakub\OswisCoreBundle\Entity\AppUser;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class ReservationManager
{

    protected $em;

    protected $mailer;

    protected $logger;

    protected $templating;

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
    }

    final public function create(
        ?Nameable $nameable = null,
        ?JobFairUser $customer = null,
        ?\DateTime $confirmedByUser = null,
        ?\DateTime $confirmedByManager = null,
        ?\DateTime $customerEditLocked = null
    ): Reservation {
        $em = $this->em;
        try {
            $entity = new Reservation(
                $nameable,
                $customer,
                $confirmedByUser,
                $confirmedByManager,
                $customerEditLocked
            );
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created reservation: '.$entity->getId().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info($e->getMessage()) : null;

            return null;
        }
    }


    /**
     * @param Reservation|null $reservation
     *
     * @return string
     * @throws \Exception
     */
    final public function sendDecreePdf(
        Reservation $reservation = null
    ): string {
        try {

            if (!$reservation) {
                throw new \Exception('Rezervace nenalezena.');
            }

            $em = $this->em;

            $customer = $reservation ? $reservation->getCustomer() : null;
            $authorAppUser = $reservation->getCreatedAuthor();

            $title = 'Ubytovací dekret';

            $givenName = ($customer && $customer->getAppUser()) ? $customer->getAppUser()->getGivenName() : null;

            $pdfString = $this->createDecreePdfString($reservation);

            $message = new \Swift_Message(self::mime_header_encode($title));

            $message->setTo(array($customer->getAppUser()->getEmail() => $customer->getFullName()))
                // ->setBcc(array('archiv@seznamovakup.cz' => $this->mime_header_encode('Archiv Seznamováku UP')))
                ->setFrom(
                    array(
                        'dagmar.petrzelova@upol.cz' => self::mime_header_encode('Mgr. Dagmar Petrželová'),
                    )
                )
                ->setSender('dagmar.petrzelova@upol.cz')
                // ->setReturnPath('karlov@jakubzak.eu')
                ->setCharset('UTF-8')
                ->setPriority(\Swift_Mime_SimpleMessage::PRIORITY_NORMAL);

            // $cidLogo = $message->embed(\Swift_Image::fromPath('../public/img/web/logo-whitebg.png'));

            $attachment = new \Swift_Attachment(
                $pdfString,
                'PrF_Karlov_doklad_'.$reservation->getId().'.pdf',
                'application/pdf'
            );
            $message->attach($attachment);

            $message->setBody(
                $this->templating->render(
                    '@ZakjakubOswisAccommodation/e-mail/payment.html.twig',
                    array(
                        'reservation' => $reservation,
                        'customer'    => $customer,
                        'payment'     => $reservation,
                        // 'logo'           => $cidLogo,
                    )
                ),
                'text/html'
            );

            $message->addPart(
                $this->templating->render(
                    '@ZakjakubOswisAccommodation/e-mail/payment.txt.twig',
                    array(
                        'reservation' => $reservation,
                        'customer'    => $customer,
                        'payment'     => $reservation,
                        // 'logo'           => $cidLogo,
                    )
                ),
                'text/plain'
            );

            if ($this->mailer->send($message)) {
                return true;
            }

            throw new \Exception();
        } catch (\Exception $e) {
            throw new \Exception('Problém s odesláním pokladního dokladu.  '.$e->getMessage());
        }
    }

    /// TODO: Multiple decrees.

    /**
     * @param Reservation $reservation
     *
     * @return string
     * @throws \Exception
     */
    final public function createDecreePdfString(
        Reservation $reservation = null
    ): string {
        try {
            if (!$reservation) {
                throw new \Exception('Rezervace nenalezena.');
            }

            $facility = $reservation->getFacilities()->first();
            if (!$facility) {
                throw new \Exception('Ubytovací objekt nenalezen.');
            }
            \assert($facility instanceof Facility);

            $author = $this->getAccommodationUser();
            if (!$author) {
                throw new \Exception('Autor nenalezen.');
            }
            $organization = $facility ? $facility->getOrganization() : null;
            if (!$organization) {
                throw new \Exception('Organizace nenalezena.');
            }
            $customer = $reservation ? $reservation->getCustomer() : null;
            if (!$customer) {
                throw new \Exception('Zákazník nenalezen.');
            }

            $roomsString = '';
            foreach ($reservation->getPersonStays() as $personStay) {
                \assert($personStay instanceof PersonStay);
                if ('' !== $roomsString) {
                    $roomsString .= ', ';
                }
                $roomsString .= $personStay->getRoom() ? $personStay->getRoom()->getShortName() : '';
            }

            $mpdf = new Mpdf(['format' => 'A4', 'mode' => 'utf-8']);
            $mpdf->SetTitle('Ubytovací dekret');
            $mpdf->SetAuthor($author ? $author->getFullName() : 'OSWIS');
            $mpdf->SetCreator($author ? $author->getFullName() : 'OSWIS');
            $mpdf->SetSubject('Ubytovací dekret');
            $mpdf->SetKeywords('ubytování,doklad,dekret');
            $mpdf->showImageErrors = true;

            $content = $this->templating->render(
                '@ZakjakubOswisAccommodation/documents/accommodation-decree.html.twig',
                array(
                    'reservation'  => $reservation,
                    'roomsString'  => $roomsString,
                    'author'       => $author,
                    'organization' => $organization,
                    'customer'     => $customer,
                )
            );

            $mpdf->WriteHTML($content);
            $pdfString = $mpdf->Output('', 'S');

            if ($pdfString) {
                return $pdfString;
            }

            throw new \Exception();
        } catch (\Exception $e) {
            throw new \Exception(' Problém s generováním ubytovacího dekretu ve formátu PDF. '.$e->getMessage());
        }
    }

    public static function mime_header_encode(string $text, string $encoding = 'utf-8'): string
    {
        return '=?'.$encoding.'?B?'.base64_encode($text).'?=';
    }

    /**
     * @return JobFairUser
     * @throws \Exception
     */
    public function getAccommodationUser(): JobFairUser
    {
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
        \assert($accommodationUser instanceof JobFairUser);
        if (!$accommodationUser) {
            throw new AccessDeniedException('Neznámý uživatel ubytovacího systému.');
        }

        return $accommodationUser;
    }

}
