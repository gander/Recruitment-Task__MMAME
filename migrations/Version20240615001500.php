<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240615001500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create contracts table and insert sample data';
    }

    public function up(Schema $schema): void
    {
        // Create the contracts table
        $this->addSql('CREATE TABLE contracts (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            column1 VARCHAR(255) DEFAULT NULL,
            nazwa_przedsiebiorcy VARCHAR(255) NOT NULL,
            column3 VARCHAR(255) DEFAULT NULL,
            nip VARCHAR(10) NOT NULL,
            column5 VARCHAR(255) DEFAULT NULL,
            column6 VARCHAR(255) DEFAULT NULL,
            column7 VARCHAR(255) DEFAULT NULL,
            column8 VARCHAR(255) DEFAULT NULL,
            column9 VARCHAR(255) DEFAULT NULL,
            kwota NUMERIC(10, 2) NOT NULL CHECK (kwota BETWEEN 1 AND 20)
        )');

        // Insert sample data
        $this->addSql("INSERT INTO contracts (column1, nazwa_przedsiebiorcy, column3, nip, column5, column6, column7, column8, column9, kwota) VALUES
('REF001', 'ABC Sp. z o.o.', 'Typ A', '1234567890', 'Aktywny', 'Warszawa', '2024-01-15', 'Jan Kowalski', 'Podpisana', 5.50),
('REF002', 'XYZ S.A.', 'Typ B', '2345678901', 'Oczekujący', 'Kraków', '2024-01-20', 'Anna Nowak', 'W trakcie', 7.75),
('REF003', 'DEF Przedsiębiorstwo', 'Typ C', '3456789012', 'Zakończony', 'Gdańsk', '2024-02-01', 'Piotr Wiśniewski', 'Zamknięta', 3.25),
('REF004', 'GHI Trading', 'Typ A', '4567890123', 'Aktywny', 'Wrocław', '2024-02-10', 'Maria Dąbrowska', 'Podpisana', 15.99),
('REF005', 'JKL Consulting', 'Typ D', '5678901234', 'Anulowany', 'Poznań', '2024-02-15', 'Tomasz Lewandowski', 'Anulowana', 4.50),
('REF006', 'MNO Services', 'Typ B', '6789012345', 'Aktywny', 'Szczecin', '2024-03-01', 'Katarzyna Zielińska', 'Podpisana', 6.80),
('REF007', 'PQR Systems', 'Typ A', '7890123456', 'W trakcie', 'Lublin', '2024-03-05', 'Michał Szymański', 'Negocjacje', 12.90),
('REF008', 'STU Solutions', 'Typ C', '8901234567', 'Oczekujący', 'Bydgoszcz', '2024-03-12', 'Agnieszka Wozniak', 'W trakcie', 2.30),
('REF009', 'VWX Corporation', 'Typ E', '9012345678', 'Aktywny', 'Katowice', '2024-03-18', 'Robert Kozłowski', 'Podpisana', 7.65),
('REF010', 'YZA Industries', 'Typ B', '0123456789', 'Zakończony', 'Białystok', '2024-04-02', 'Małgorzata Jankowska', 'Zamknięta', 18.40)");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE contracts');
    }
}
