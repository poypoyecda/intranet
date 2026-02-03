<?php
session_start();

// Récupérer la citation du jour via le controller
require_once __DIR__ . '/../../controller/citations.php';

$citationController = new CitationController();
$citationDuJour = $citationController->getDailyCitation();
?>

<head>
    <?php include_once '../templates/head.php'; ?>
</head>

<body class="bodyhome">
    <div class="container-fluid md-6">
        <h1 class="titrehome">Citation du jour :</h1>
    </div>
    <div class="container-md-6">
        <h2 class="citation"><?php echo htmlspecialchars($citationDuJour['nom']); ?></h2>
        <h2 class="citation">"<?php echo htmlspecialchars($citationDuJour['description']); ?>"</h2>
    </div>
</body>