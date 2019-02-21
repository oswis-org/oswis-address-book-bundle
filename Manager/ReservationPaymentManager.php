<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Mpdf\Mpdf;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAccommodationBundle\Entity\Facility;
use Zakjakub\OswisAccommodationBundle\Entity\ReservationPayment;

class ReservationPaymentManager
{

    protected $em;

    protected $mailer;

    protected $logger;

    protected $templating;

    public function __construct(
        EntityManagerInterface $em,
        \Swift_Mailer $mailer,
        LoggerInterface $logger,
        \Twig_Environment $templating
    ) {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->templating = $templating;
    }

    /**
     * @param ReservationPayment|null $payment
     *
     * @return string
     * @throws \Exception
     */
    final public function sendReceiptPdf(
        ReservationPayment $payment = null
    ): string {
        try {

            if (!$payment) {
                throw new \Exception('Platba nenalezena.');
            }

            $em = $this->em;

            $reservation = $payment->getReservation();
            $customer = $reservation ? $reservation->getCustomer() : null;
            $author = $payment->getAuthor();
            $title = 'Potvrzení o platbě';

            $givenName = ($customer && $customer->getAppUser()) ? $customer->getAppUser()->getGivenName() : null;

            $pdfString = $this->createReceiptPdfString($payment);

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
                'PrF_Karlov_doklad_'.$payment->getId().'.pdf',
                'application/pdf'
            );
            $message->attach($attachment);

            $message->setBody(
                $this->templating->render(
                    '@ZakjakubOswisAccommodation/e-mail/payment.html.twig',
                    array(
                        'reservation' => $reservation,
                        'customer'    => $customer,
                        'payment'     => $payment,
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
                        'payment'     => $payment,
                        // 'logo'           => $cidLogo,
                    )
                ),
                'text/plain'
            );

            if ($this->mailer->send($message)) {
                $payment->setMailConfirmationSend('reservation-payment-manager');
                $em->persist($payment);
                $em->flush();

                return true;
            }

            throw new \Exception();
        } catch (\Exception $e) {
            throw new \Exception('Problém s odesláním pokladního dokladu.  '.$e->getMessage());
        }
    }

    /// TODO: Multiple receipts.

    /**
     * @param ReservationPayment|null $payment
     *
     * @return string
     * @throws \Exception
     */
    final public function createReceiptPdfString(
        ReservationPayment $payment = null
    ): string {
        try {

            if (!$payment) {
                throw new \Exception('Platba nenalezena.');
            }

            $em = $this->em;

            $title = 'Příjmový/výdajový pokladní doklad';
            $subTitle = '';
            $reservation = $payment->getReservation();
            if (!$reservation) {
                throw new \Exception('Rezervace nenalezena.');
            }
            $facility = $reservation->getFacilities()->first();
            if (!$facility) {
                throw new \Exception('Ubytovací objekt nenalezen.');
            }
            \assert($facility instanceof Facility);
            $author = $payment->getAuthor();
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

            if ($payment->getNumericValue() > 0) {
                $title = 'Příjmový pokladní doklad';
            } elseif ($payment->getNumericValue() < 0) {
                $title = 'Výdajový pokladní doklad';
            }

            if ($payment->getId()) {
                $subTitle .= ' č. '.$payment->getId();
            }

            if ($payment->getCreatedDateTime()) {
                $subTitle .= ' ze dne '.$payment->getCreatedDateTime()->format('j. n. Y');
            }

            $mpdf = new Mpdf(['format' => 'A4', 'mode' => 'utf-8']);
            $mpdf->SetTitle($title);
            $mpdf->SetAuthor($author ? $author->getFullName() : 'OSWIS');
            $mpdf->SetCreator($author ? $author->getFullName() : 'OSWIS');
            $mpdf->SetSubject($title.' '.$subTitle);
            $mpdf->SetKeywords('ubytování,doklad,platba');
            $mpdf->showImageErrors = true;

            //                 '@ZakjakubOswisAccommodation/documents/accommodation-decret.html.twig',

            $content = $this->templating->render(
                '@ZakjakubOswisAccommodation/documents/cash-receipt.html.twig',
                array(
                    'title'        => $title,
                    'subTitle'     => $subTitle,
                    'payment'      => $payment,
                    'author'       => $author,
                    'organization' => $organization,
                    'type'         => $payment->getType(),
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
            throw new \Exception('Problém s generováním pokladního dokladu ve formátu PDF.'.$e->getMessage());
        }
    }

    public static function mime_header_encode(string $text, string $encoding = 'utf-8'): string
    {
        return '=?'.$encoding.'?B?'.base64_encode($text).'?=';
    }
}
