<?php

declare(strict_types=1);

namespace OswisOrg\OswisAddressBookBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class DorucitelnaDomenaValidator extends ConstraintValidator
{
    /**
     * Domény, u kterých má smysl nabídnout opravu překlepu.
     *
     * Záměrně krátký seznam toho, co se u studentů UP reálně vyskytuje — širší seznam by
     * jen zvyšoval šanci, že navrhneme nesmysl. Návrh se ukazuje jen tehdy, když je zadaná
     * doména SOUČASNĚ nedoručitelná, takže nehrozí, že bychom někoho opravovali zbytečně.
     */
    private const array ZNAME_DOMENY = [
        'gmail.com', 'seznam.cz', 'email.cz', 'centrum.cz', 'post.cz', 'volny.cz',
        'outlook.com', 'outlook.cz', 'hotmail.com', 'hotmail.cz', 'icloud.com',
        'yahoo.com', 'proton.me', 'protonmail.com', 'upol.cz',
    ];

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DorucitelnaDomena) {
            throw new UnexpectedTypeException($constraint, DorucitelnaDomena::class);
        }
        if (null === $value || '' === $value || !is_string($value)) {
            return;
        }
        $zavinac = strrpos($value, '@');
        if (false === $zavinac) {
            return; // Tvar řeší `Email` constraint, ne tenhle.
        }
        $domena = strtolower(trim(substr($value, $zavinac + 1)));
        if ('' === $domena || !str_contains($domena, '.')) {
            return;
        }

        if ($this->prijimaPostu($domena)) {
            return;
        }

        $navrh = $this->najdiPodobnou($domena);
        if (null !== $navrh) {
            $this->context->buildViolation($constraint->messageSNavrhem)
                ->setParameter('{{ domena }}', $domena)
                ->setParameter('{{ navrh }}', substr($value, 0, $zavinac + 1).$navrh)
                ->addViolation();

            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ domena }}', $domena)
            ->addViolation();
    }

    /**
     * Bez MX je podle RFC 5321 implicitním cílem A/AAAA záznam — proto se ptáme na obojí.
     *
     * ⚠️ TEČKA NA KONCI je to nejdůležitější v celé metodě. Bez ní si resolver doplní `search`
     * domény z `/etc/resolv.conf` a dotaz na `gmal.com` se zodpoví jako `gmal.com.cdwifi.cz`.
     * Ověřeno při psaní: bez tečky hlásil A záznam i neexistující TLD `seznam.c`, takže by
     * kontrola nezachytila vůbec nic — a test by přesto svítil zeleně, protože by validátor
     * prostě nikdy nic nenamítl. `checkdnsrr()` má tutéž vadu, proto tu není.
     *
     * ⚠️ Když DNS neodpoví, vrací se `true`. Registrace se nesmí zastavit kvůli tomu, že nám
     * zrovna nefunguje překlad jmen — falešné zamítnutí je horší než propuštěný překlep.
     * Na produkci to není teorie: IMAP tam dvakrát denně padá právě na dočasné nedostupnosti DNS.
     */
    private function prijimaPostu(string $domena): bool
    {
        $fqdn = $domena.'.';
        foreach ([DNS_MX, DNS_A, DNS_AAAA] as $typ) {
            $zaznamy = @dns_get_record($fqdn, $typ);
            if (!is_array($zaznamy)) {
                return true; // Chyba dotazu (ne „neexistuje") — propouštíme.
            }
            if ([] !== $zaznamy) {
                return true;
            }
        }

        return false;
    }

    private function najdiPodobnou(string $domena): ?string
    {
        $nejlepsi = null;
        $nejmensi = PHP_INT_MAX;
        foreach (self::ZNAME_DOMENY as $znama) {
            $vzdalenost = levenshtein($domena, $znama);
            // Dva překlepy jsou strop: při třech už návrh přestává být důvěryhodný
            // („abcd.cz" není překlep „gmail.com").
            if ($vzdalenost <= 2 && $vzdalenost < $nejmensi) {
                $nejmensi = $vzdalenost;
                $nejlepsi = $znama;
            }
        }

        return $nejlepsi;
    }
}
