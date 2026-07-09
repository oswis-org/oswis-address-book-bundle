<?php

declare(strict_types=1);

namespace OswisOrg\OswisAddressBookBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use OswisOrg\OswisAddressBookBundle\Entity\Person;
use OswisOrg\OswisCoreBundle\Entity\AppUser\AppUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Napraví jména poškozená parserem jmen (viz PersonNameOnlyTrait::setFullName()).
 *
 * Bez `--apply` jen vypíše, co by udělal. Opravuje se VŽDY přes entitu, nikdy přímým SQL:
 * `getName()` u osoby volá `updateName()`, které jméno přepočítá z `givenName`/`familyName`,
 * takže `UPDATE ... SET name=...` by se při prvním čtení entity zase zahodilo.
 *
 * Řeší tři skupiny:
 *  A) jméno rozseknuté na pomlčce („Anna-Líza" → „Anna -Líza"; příjmení začíná pomlčkou),
 *  B) uživatelský účet bez jména, jehož kontakt jméno má (fosilie po zničené registraci),
 *  C) kontakty bez jména — jméno je nenávratně pryč, příkaz je jen vypíše k ručnímu doplnění.
 */
#[AsCommand(
    name: 'oswis:contacts:repair-names',
    description: 'Najde (a s --apply opraví) kontakty a účty s poškozeným jménem.',
)]
final class RepairContactNamesCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('apply', null, InputOption::VALUE_NONE, 'Provést zápis (jinak jen výpis).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $apply = (bool) $input->getOption('apply');
        $io->title($apply ? 'Náprava jmen — ZÁPIS' : 'Náprava jmen — jen výpis (dry-run)');

        $changed = $this->repairMangled($io, $apply);
        $changed += $this->repairAppUsers($io, $apply);
        $this->reportNameless($io);

        if ($apply && $changed > 0) {
            $this->em->flush();
            $io->success("Zapsáno změn: $changed.");
        } elseif ($apply) {
            $io->success('Nebylo co zapisovat.');
        } else {
            $io->note("Navrženo změn: $changed. Spusť znovu s --apply.");
        }

        return Command::SUCCESS;
    }

    /** A) Příjmení začínající pomlčkou = otisk toho, že parser rozsekl jednoslovné jméno. */
    private function repairMangled(SymfonyStyle $io, bool $apply): int
    {
        /** @var list<Person> $people */
        $people = $this->em->createQueryBuilder()
            ->select('p')->from(Person::class, 'p')
            ->where('p.familyName LIKE :dash')->setParameter('dash', '-%')
            ->getQuery()->getResult();

        $rows = [];
        $skipped = [];
        foreach ($people as $person) {
            $given = ''.$person->getGivenName();
            $repaired = $given.$person->getFamilyName();
            // Bez křestního jména by slepené jméno začínalo pomlčkou. Takový řádek vznikl jinak než
            // rozseknutím jednoslovného jména — nesahat na něj, jen ho ohlásit.
            if ('' === $given || $repaired === $person->getName()) {
                $skipped[] = [$person->getId(), $person->getName(), '' === $given ? 'chybí křestní' : 'už je v pořádku'];
                continue;
            }
            $rows[] = [$person->getId(), $person->getName(), $repaired];
            if ($apply) {
                $person->setName($repaired);
            }
        }
        $io->section('A) Zkomolená jména (pomlčka)');
        [] === $rows ? $io->text('Žádná.') : $io->table(['ID', 'nyní', 'opraveno na'], $rows);
        if ([] !== $skipped) {
            $io->warning('Přeskočeno (opravit ručně):');
            $io->table(['ID', 'jméno', 'důvod'], $skipped);
        }

        return count($rows);
    }

    /** B) AppUser bez jména, jehož kontakt jméno má. Vzniklo tím, že registrace přišla o jméno. */
    private function repairAppUsers(SymfonyStyle $io, bool $apply): int
    {
        /** @var list<Person> $people */
        $people = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Person::class, 'p')
            ->innerJoin('p.appUser', 'u')
            ->where('u.name IS NULL OR u.name = :empty')
            ->andWhere('p.name IS NOT NULL')
            ->andWhere('p.name <> :empty')
            ->setParameter('empty', '')
            ->getQuery()->getResult();

        $rows = [];
        foreach ($people as $person) {
            $user = $person->getAppUser();
            if (!$user instanceof AppUser) {
                continue;
            }
            $rows[] = [$user->getId(), $user->getEmail(), $person->getName()];
            if ($apply) {
                $user->setName($person->getName());
            }
        }
        $io->section('B) Účty bez jména, kde kontakt jméno má');
        [] === $rows ? $io->text('Žádné.') : $io->table(['AppUser', 'e-mail', 'doplní se jméno'], $rows);

        return count($rows);
    }

    /** C) Kontakty bez jména — původní jméno je nenávratně pryč, opravit ho lze jen dotazem na člověka. */
    private function reportNameless(SymfonyStyle $io): void
    {
        /** @var list<Person> $people */
        $people = $this->em->createQueryBuilder()
            ->select('p')->from(Person::class, 'p')
            ->where('p.name IS NULL OR p.name = :empty')->setParameter('empty', '')
            ->getQuery()->getResult();

        $io->section('C) Kontakty bez jména (nutný dotaz na člověka)');
        if ([] === $people) {
            $io->text('Žádné.');

            return;
        }
        $io->table(
            ['kontakt', 'e-mail', 'telefon'],
            array_map(
                static fn (Person $p): array => [$p->getId(), $p->getEmail(), $p->getPhone()],
                $people,
            ),
        );
        $io->warning('Tato jména příkaz doplnit neumí — nikde v systému už nejsou uložená.');
    }
}
