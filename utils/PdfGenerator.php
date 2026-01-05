<?php
/**
 * PDF Generator for Project Reports
 * Uses TCPDF library
 * 
 * Installation: composer require tecnickcom/tcpdf
 * Or download from: https://tcpdf.org/
 */

// Manual TCPDF installation
require_once(__DIR__ . '/../libs/tcpdf/tcpdf.php');

class PdfGenerator
{
    private $pdf;

    public function __construct()
    {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $this->setupPdf();
    }

    private function setupPdf()
    {
        // Document metadata
        $this->pdf->SetCreator('Laboratoire Universitaire');
        $this->pdf->SetAuthor('Administration');
        $this->pdf->SetTitle('Rapport des Projets');

        // Remove default header/footer
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // Set margins
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 15);

        // Set font
        $this->pdf->SetFont('helvetica', '', 10);
    }

    /**
     * Generate PDF report for projects
     */
    public function generateProjectsReport($projets, $title = 'Rapport des Projets', $filters = [])
    {
        // Add first page
        $this->pdf->AddPage();

        // Header
        $this->addHeader($title, $filters);

        // Summary stats
        $this->addSummary($projets);

        // Projects list
        $this->addProjectsList($projets);

        // Output PDF
        return $this->pdf->Output('rapport_projets_' . date('Y-m-d') . '.pdf', 'I');
    }

    private function addHeader($title, $filters)
    {
        // Logo/Title section
        $this->pdf->SetFont('helvetica', 'B', 18);
        $this->pdf->SetTextColor(44, 62, 80);
        $this->pdf->Cell(0, 10, $title, 0, 1, 'C');

        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->Cell(0, 6, 'Généré le ' . date('d/m/Y à H:i'), 0, 1, 'C');

        // Filters info
        if (!empty($filters)) {
            $this->pdf->Ln(5);
            $this->pdf->SetFont('helvetica', 'I', 9);
            $filterText = 'Filtres appliqués: ';
            $filterParts = [];

            if (!empty($filters['thematique'])) {
                $filterParts[] = 'Thématique: ' . $filters['thematique'];
            }
            if (!empty($filters['responsable'])) {
                $filterParts[] = 'Responsable: ' . $filters['responsable'];
            }
            if (!empty($filters['annee'])) {
                $filterParts[] = 'Année: ' . $filters['annee'];
            }

            if (!empty($filterParts)) {
                $filterText .= implode(' | ', $filterParts);
                $this->pdf->Cell(0, 5, $filterText, 0, 1, 'C');
            }
        }

        $this->pdf->Ln(8);

        // Divider line
        $this->pdf->SetDrawColor(52, 152, 219);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        $this->pdf->Ln(5);
    }

    private function addSummary($projets)
    {
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->SetTextColor(44, 62, 80);
        $this->pdf->Cell(0, 8, 'Résumé', 0, 1, 'L');

        // Count by status
        $statuts = [];
        foreach ($projets as $p) {
            $statut = $p['statut_nom'] ?? 'Non défini';
            $statuts[$statut] = ($statuts[$statut] ?? 0) + 1;
        }

        // Calculate total budget
        $budgetTotal = array_sum(array_column($projets, 'budget'));

        // Summary box
        $this->pdf->SetFillColor(240, 248, 255);
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(60, 60, 60);

        $y = $this->pdf->GetY();
        $this->pdf->RoundedRect(15, $y, 180, 30, 3, '1111', 'F');

        $this->pdf->SetXY(20, $y + 5);
        $this->pdf->Cell(85, 6, 'Nombre total de projets: ' . count($projets), 0, 0, 'L');

        $this->pdf->SetXY(105, $y + 5);
        $this->pdf->Cell(85, 6, 'Budget total: ' . number_format($budgetTotal, 0, ',', ' ') . ' DZD', 0, 1, 'L');

        $this->pdf->SetXY(20, $y + 12);
        $statusText = [];
        foreach ($statuts as $statut => $count) {
            $statusText[] = "$statut: $count";
        }
        $this->pdf->Cell(0, 6, 'Répartition: ' . implode(' | ', $statusText), 0, 1, 'L');

        $this->pdf->Ln(15);
    }

    private function addProjectsList($projets)
    {
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->SetTextColor(44, 62, 80);
        $this->pdf->Cell(0, 8, 'Liste des Projets', 0, 1, 'L');
        $this->pdf->Ln(2);

        foreach ($projets as $index => $projet) {
            // Check if we need a new page
            if ($this->pdf->GetY() > 250) {
                $this->pdf->AddPage();
            }

            $this->addProjectCard($projet, $index + 1);
        }
    }

    private function addProjectCard($projet, $number)
    {
        $y = $this->pdf->GetY();

        // Card background
        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->RoundedRect(15, $y, 180, 35, 2, '1111', 'DF');

        // Project number & title
        $this->pdf->SetXY(20, $y + 4);
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetTextColor(52, 152, 219);
        $this->pdf->Cell(10, 6, $number . '.', 0, 0, 'L');

        $this->pdf->SetTextColor(44, 62, 80);
        $titre = mb_substr($projet['titre'], 0, 70) . (mb_strlen($projet['titre']) > 70 ? '...' : '');
        $this->pdf->Cell(0, 6, $titre, 0, 1, 'L');

        // Details
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(80, 80, 80);

        // Row 1: Responsable & Thématique
        $this->pdf->SetXY(30, $y + 11);
        $responsable = ($projet['responsable_nom'] ?? '') . ' ' . ($projet['responsable_prenom'] ?? '');
        $this->pdf->Cell(85, 5, 'Responsable: ' . $responsable, 0, 0, 'L');

        $this->pdf->SetX(115);
        $thematique = $projet['thematique_nom'] ?? 'Non défini';
        $this->pdf->Cell(0, 5, 'Thématique: ' . $thematique, 0, 1, 'L');

        // Row 2: Dates & Status
        $this->pdf->SetXY(30, $y + 17);
        $dateDebut = date('d/m/Y', strtotime($projet['date_debut']));
        $dateFin = $projet['date_fin'] ? date('d/m/Y', strtotime($projet['date_fin'])) : 'En cours';
        $this->pdf->Cell(85, 5, 'Période: ' . $dateDebut . ' - ' . $dateFin, 0, 0, 'L');

        $this->pdf->SetX(115);
        $statut = $projet['statut_nom'] ?? 'Non défini';
        $this->pdf->Cell(0, 5, 'Statut: ' . $statut, 0, 1, 'L');

        // Row 3: Budget & Financement
        $this->pdf->SetXY(30, $y + 23);
        $budget = $projet['budget'] ? number_format($projet['budget'], 0, ',', ' ') . ' DZD' : 'Non défini';
        $this->pdf->Cell(85, 5, 'Budget: ' . $budget, 0, 0, 'L');

        $this->pdf->SetX(115);
        $financement = $projet['type_financement_nom'] ?? 'Non défini';
        $this->pdf->Cell(0, 5, 'Financement: ' . $financement, 0, 1, 'L');

        $this->pdf->Ln(8);
    }

    /**
     * Get the PDF instance for custom modifications
     */
    public function getPdf()
    {
        return $this->pdf;
    }
}
?>