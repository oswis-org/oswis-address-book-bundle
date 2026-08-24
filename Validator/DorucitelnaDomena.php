<?php

declare(strict_types=1);

namespace OswisOrg\OswisAddressBookBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Doména e-mailové adresy musí být schopná poštu vůbec přijmout.
 *
 * Standardní `Email` constraint kontroluje jen TVAR adresy, takže `karoline.vesela@gmal.com`
 * projde jako platná — přestože `gmal.com` nemá MX ani A záznam a žádnou poštu nepřijme.
 * Přesně tenhle překlep se stal na produkci 24. 8. 2026: účastnici nedorazil aktivační e-mail,
 * přihlásila se proto podruhé a v systému vznikly dvě přihlášky. Chyba se přitom dala odhalit
 * hned při odeslání formuláře.
 *
 * ⚠️ Zamítá se POUZE doména, která poštu prokazatelně přijmout nemůže (nemá MX ani A/AAAA —
 * bez MX je implicitním cílem podle RFC 5321 právě A záznam). Překlepy, které náhodou vedou
 * na existující doménu (`seznma.cz`, `emial.cz` — typosquaty s vlastním MX), takhle odhalit
 * nelze a formulář je propustí; na ty je potřeba jiná vrstva.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class DorucitelnaDomena extends Constraint
{
    public string $message = 'Doména {{ domena }} nepřijímá poštu — zkontroluj prosím adresu.';

    public string $messageSNavrhem = 'Doména {{ domena }} nepřijímá poštu. Nemá tam být {{ navrh }}?';
}
