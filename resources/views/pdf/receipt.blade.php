<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quittance de Loyer</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; line-height: 1.6; color: #222; margin: 40px; }
        .header { text-align: center; border-bottom: 2px solid #f8b301; padding-bottom: 20px; }
        .brand { color: #f8b301; font-size: 24px; font-weight: bold; }
        .details { margin-top: 30px; }
        .box { border: 1px solid #ddd; padding: 15px; background: #fafafa; }
        .footer { margin-top: 50px; text-align: center; font-size: 11px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">ESTATIQ ERP</div>
        <h2>QUITTANCE DE LOYER</h2>
    </div>

    <div class="details">
        <p><strong>Locataire :</strong> {{ $payment->tenant->full_name }}</p>
        <p><strong>Date de paiement :</strong> {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</p>
        <p><strong>Mode de règlement :</strong> {{ ucfirst($payment->method) }}</p>
    </div>

    <div class="box">
        <p>Je soussigné, gestionnaire d'ESTATIQ, déclare avoir reçu la somme de :</p>
        <h3 style="text-align: center;">{{ number_format($payment->amount, 2, ',', ' ') }} €</h3>
        <p>en règlement du loyer pour la période concernée.</p>
    </div>

    <div class="footer">
        Fait le {{ now()->format('d/m/Y') }} à Marrakech.<br>
        Ceci est un document officiel généré électroniquement.
    </div>
</body>
</html>