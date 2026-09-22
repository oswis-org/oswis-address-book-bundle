<?php

declare(strict_types=1);

namespace OswisOrg\OswisAddressBookBundle\Controller;

use OswisOrg\OswisAddressBookBundle\Entity\Position;
use OswisOrg\OswisAddressBookBundle\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Kdo je v organizaci — pozice osob ve spolku, od kdy a do kdy.
 *
 * PROČ vznikla (19. 9. 2026): `Position` existuje od 21. 2. 2019, má datum od i do, typy
 * (člen / manažer / ředitel / zaměstnanec) a v datech leželo **41 pozic, z toho 23 aktivních**
 * — ale **v administraci pro ně nebyla jediná obrazovka**. Editovat je šlo jen přes API.
 * Poslední zápis je z **1. 7. 2023**: evidence tiše zastarala o tři roky, protože ji nebylo kde
 * vést. Přesně vzorec model → ⛔ obrazovka → data → uživatel
 * (viz `docs/OSWIS_INVENTURA_NEDOTAZENEHO_2026-09-19.md`).
 *
 * **Zatím jen ke čtení.** Nejdřív ať je vidět, co v datech je; teprve pak má smysl řešit zápis.
 *
 * Proč je to důležité: tahle rovina odpovídá na „kdo je v organizačním týmu a od kdy", což
 * z přihlášek (ty jsou na jednu akci) nejde odvodit — a živí veřejnou stránku „O nás"
 * (`OrganizationController::showTeam()`), která se dnes píše ručně jinde.
 */
#[IsGranted('ROLE_MANAGER')]
final class WebAdminPositionController extends AbstractController
{
    public function __construct(private readonly PositionRepository $positionRepository)
    {
    }

    public function index(): Response
    {
        $vsechny = [];
        foreach ($this->positionRepository->getPositions() as $pozice) {
            if ($pozice instanceof Position) {
                $vsechny[] = $pozice;
            }
        }

        return $this->render('@OswisOrgOswisAddressBook/web_admin/position/index.html.twig', [
            'organizace'      => $this->seskupitPodleOrganizaci($vsechny),
            'celkem'          => count($vsechny),
            'posledniZmena'   => $this->posledniZmena($vsechny),
            'pageTitle'       => 'Pozice v organizaci',
            'page_title'      => 'Pozice :: ADMIN',
        ]);
    }

    /**
     * Pozice po organizacích; uvnitř nejdřív aktivní (bez data do), pak ukončené.
     *
     * @param list<Position> $pozice
     *
     * @return list<array{nazev: string, aktivni: list<Position>, ukoncene: list<Position>}>
     */
    private function seskupitPodleOrganizaci(array $pozice): array
    {
        /** @var array<string, array{nazev: string, aktivni: list<Position>, ukoncene: list<Position>}> $podle */
        $podle = [];
        foreach ($pozice as $p) {
            $nazev = $p->getOrganization()?->getName() ?? '(bez organizace)';
            $podle[$nazev] ??= ['nazev' => $nazev, 'aktivni' => [], 'ukoncene' => []];
            $podle[$nazev][null === $p->getEndDateTime() ? 'aktivni' : 'ukoncene'][] = $p;
        }
        foreach (array_keys($podle) as $nazev) {
            // Aktivní podle data nástupu od nejstaršího — nahoře ti, kdo jsou v týmu nejdéle.
            usort(
                $podle[$nazev]['aktivni'],
                static fn (Position $a, Position $b): int => ($a->getStartDateTime()?->getTimestamp() ?? 0)
                    <=> ($b->getStartDateTime()?->getTimestamp() ?? 0),
            );
            // Ukončené od naposledy odešlých.
            usort(
                $podle[$nazev]['ukoncene'],
                static fn (Position $a, Position $b): int => ($b->getEndDateTime()?->getTimestamp() ?? 0)
                    <=> ($a->getEndDateTime()?->getTimestamp() ?? 0),
            );
        }
        $radky = array_values($podle);
        // Největší organizace první.
        usort($radky, static fn (array $a, array $b): int => count($b['aktivni']) <=> count($a['aktivni']));

        return $radky;
    }

    /**
     * Kdy se s evidencí naposledy něco dělo.
     *
     * Je to na stránce schválně: právě tenhle údaj ukazuje, jestli je evidence živá, nebo
     * jestli se do ní roky nikdo nepodíval (k 19. 9. 2026 byl poslední zápis z 1. 7. 2023).
     *
     * @param list<Position> $pozice
     */
    private function posledniZmena(array $pozice): ?\DateTimeInterface
    {
        $posledni = null;
        foreach ($pozice as $p) {
            foreach ([$p->getUpdatedAt(), $p->getCreatedAt(), $p->getEndDateTime(), $p->getStartDateTime()] as $datum) {
                if ($datum instanceof \DateTimeInterface && (null === $posledni || $datum > $posledni)) {
                    $posledni = $datum;
                }
            }
        }

        return $posledni;
    }
}
