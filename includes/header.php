<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?><?= SITE_NAME ?></title>
    <meta name="description" content="<?= SITE_DESCRIPTION ?>">
    
    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= getBasePath() ?>assets/css/style.css" rel="stylesheet">
    
    <!-- Razorpay -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <!-- Top Banner -->
    <div class="top-banner">
        <div class="container">
            <div class="marquee">
                <span>WORLDWIDE EXPRESS SHIPPING • Trusted by 1 Lakh + Parents • Buy 2 & Save 5% | Buy 3+ & Save 10%</span>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/navigation.php'; ?>
