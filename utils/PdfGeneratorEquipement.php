<?php
require_once(__DIR__ . '/../libs/tcpdf/tcpdf.php');

class PdfGeneratorEquipement
{
    private $pdf;

    public function __construct()
    {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $this->setupPdf();
    }

    private function setupPdf()
    {
        $this->pdf->SetCreator('Laboratoire Universitaire');
        $this->pdf->SetAuthor('Administration');
        $this->pdf->SetTitle('Rapport des Équipements');
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 15);
        $this->pdf->SetFont('helvetica', '', 10);
    }

    public function generateEquipementsReport($equipements, $stats, $filters = [])
    {
        $this->pdf->AddPage();
        $this->addHeader($filters);
        $this->addSummary($stats);
        $this->addEquipementsList($equipements);
        return $this->pdf->Output('rapport_equipements_' . date('Y-m-d') . '.pdf', 'I');
    }

    private function addHeader($filters)
    {
        $this->pdf->SetFont('helvetica', 'B', 18);
        $this->pdf->SetTextColor(44, 62, 80);
        $this->pdf->Cell(0, 10, 'Rapport des Équipements', 0, 1, 'C');

        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->Cell(0, 6, 'Généré le ' . date('d/m/Y à H:i'), 0, 1, 'C');

        $this->pdf->Ln(5);
        $this->pdf->SetDrawColor(52, 152, 219);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        $this->pdf->Ln(5);
    }

    private function addSummary($stats)
    {
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'Résumé', 0, 1);

        $this->pdf->SetFillColor(240, 248, 255);
        $this->pdf->SetFont('helvetica', '', 10);

        $y = $this->pdf->GetY();
        $this->pdf->RoundedRect(15, $y, 180, 25, 3, '1111', 'F');

        $this->pdf->SetXY(20, $y + 5);
        $this->pdf->Cell(85, 6, 'Total équipements: ' . $stats['total'], 0, 0);
        $this->pdf->SetX(105);
        $this->pdf->Cell(85, 6, 'En utilisation: ' . $stats['en_utilisation'], 0, 1);

        $this->pdf->SetXY(20, $y + 12);
        $this->pdf->Cell(0, 6, 'Taux d\'occupation: ' . $stats['taux_occupation'] . '%', 0, 1);

        $this->pdf->Ln(15);
    }

    private function addEquipementsList($equipements)
    {
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'Liste des Équipements', 0, 1);
        $this->pdf->Ln(2);

        foreach ($equipements as $index => $eq) {
            if ($this->pdf->GetY() > 250) {
                $this->pdf->AddPage();
            }
            $this->addEquipementCard($eq, $index + 1);
        }
    }

    private function addEquipementCard($eq, $number)
    {
        $y = $this->pdf->GetY();

        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->RoundedRect(15, $y, 180, 30, 2, '1111', 'DF');

        $this->pdf->SetXY(20, $y + 4);
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetTextColor(52, 152, 219);
        $this->pdf->Cell(10, 6, $number . '.', 0, 0);

        $this->pdf->SetTextColor(44, 62, 80);
        $this->pdf->Cell(0, 6, mb_substr($eq['nom'], 0, 70), 0, 1);

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(80, 80, 80);

        $this->pdf->SetXY(30, $y + 11);
        $this->pdf->Cell(85, 5, 'Type: ' . (TYPES_EQUIPEMENTS[$eq['type']] ?? $eq['type']), 0, 0);
        $this->pdf->SetX(115);
        $this->pdf->Cell(0, 5, 'État: ' . (ETATS_EQUIPEMENTS[$eq['etat']] ?? $eq['etat']), 0, 1);

        $this->pdf->SetXY(30, $y + 17);
        $desc = mb_substr($eq['description'], 0, 100) . '...';
        $this->pdf->MultiCell(160, 5, 'Description: ' . $desc, 0, 'L');

        $this->pdf->Ln(8);
    }
}
?>