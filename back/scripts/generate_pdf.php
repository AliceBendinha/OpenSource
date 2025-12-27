<?php
// scripts/generate_pdf.php
// Usage example: generate a simple quarterly PDF for a pharmacy
require __DIR__ . '/../config/bootstrap.php';
use Dompdf\Dompdf;

if (!class_exists(Dompdf::class)) {
    echo json_encode(['error'=>'DomPDF não encontrado. Execute: composer require dompdf/dompdf']);
    exit;
}

$pharmacy_id = $argv[1] ?? null;
if (!$pharmacy_id) { echo json_encode(['error'=>'Passe o id da farmácia como argumento']); exit; }

$stmt = $pdo->prepare('SELECT * FROM farmacias WHERE id = ?');
$stmt->execute([$pharmacy_id]);
$f = $stmt->fetch();
if (!$f) { echo json_encode(['error'=>'Farmácia não encontrada']); exit; }

// gather top products/services (simplified)
$topP = $pdo->prepare('SELECT p.nome, COUNT(*) as cnt FROM pesquisas pe JOIN produtos p ON p.id = pe.farmacia_id WHERE pe.farmacia_id = ? GROUP BY p.id ORDER BY cnt DESC LIMIT 10');
$topP->execute([$pharmacy_id]);
$produtos = $topP->fetchAll();

$html = '<h1>Relatório trimestral — ' . htmlspecialchars($f['nome']) . '</h1>';
$html .= '<p>Gerado em ' . date('Y-m-d H:i') . '</p>';
$html .= '<h2>Produtos mais pesquisados</h2><ul>';
foreach ($produtos as $p) $html .= '<li>' . htmlspecialchars($p['nome']) . ' — ' . $p['cnt'] . '</li>';
$html .= '</ul>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$file = __DIR__ . '/../storage/pdfs/relatorio_pharm_' . $pharmacy_id . '.pdf';
file_put_contents($file, $dompdf->output());
echo json_encode(['success'=>true,'file'=>$file]);
