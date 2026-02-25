<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Quittance de Loyer</title>
    <style>
        body { 
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif; 
            color: #333; 
            line-height: 1.5; 
            font-size: 14px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 40px; 
            border-bottom: 2px solid #1f2937; 
            padding-bottom: 20px; 
        }
        .title { 
            font-size: 28px; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 2px;
            color: #1f2937;
        }
        .agency-name {
            font-size: 18px;
            color: #6b7280;
            margin-top: 5px;
        }
        .box { 
            border: 1px solid #e5e7eb; 
            padding: 20px; 
            margin-bottom: 30px; 
            background-color: #f9fafb; 
            border-radius: 5px;
        }
        .tenant-name {
            font-size: 20px;
            color: #111827;
            margin: 10px 0;
        }
        .details-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 30px; 
        }
        .details-table th, .details-table td { 
            border-bottom: 1px solid #e5e7eb; 
            padding: 12px 10px; 
            text-align: left; 
        }
        .details-table th { 
            background-color: #f3f4f6; 
            font-weight: bold;
            color: #374151;
        }
        .total-row th {
            font-size: 16px;
            border-top: 2px solid #1f2937;
        }
        .legal-text {
            margin-top: 40px; 
            font-size: 11px; 
            color: #9ca3af;
            text-align: justify;
        }
        .signature { 
            margin-top: 50px; 
            text-align: right; 
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">QUITTANCE DE LOYER</div>
        <div class="agency-name">Agence ESTATIQ</div>
        <p style="color: #6b7280;">Émise le : {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
    </div>

    <div class="box">
        <strong>Adresse du bien loué :</strong><br>
        <span style="font-size: 16px; font-weight: bold;">{{ $payment->tenant->property->title ?? 'Propriété non assignée' }}</span><br>
        {{ $payment->tenant->property->address ?? 'Adresse inconnue' }}
    </div>

    <div class="content">
        <p>Je soussigné(e), représentant l'agence ESTATIQ, certifie avoir reçu de :</p>
        <h3 class="tenant-name">{{ $payment->tenant->full_name }}</h3>
        
        <p>La somme de : <strong style="font-size: 18px;">{{ number_format($payment->amount, 2, ',', ' ') }} €</strong></p>
        <p>Payée le : <strong>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</strong> par <strong>{{ ucfirst($payment->method) }}</strong>.</p>

        <table class="details-table">
            <tr>
                <th>Désignation</th>
                <th style="text-align: right;">Montant</th>
            </tr>
            <tr>
                <td>Loyer et provisions pour charges</td>
                <td style="text-align: right;">{{ number_format($payment->amount, 2, ',', ' ') }} €</td>
            </tr>
            <tr class="total-row">
                <th>Total Payé</th>
                <th style="text-align: right;">{{ number_format($payment->amount, 2, ',', ' ') }} €</th>
            </tr>
        </table>

        <p class="legal-text">
            Cette quittance annule tous les reçus qui auraient pu être donnés pour acompte versé sur le présent terme.
            Le paiement de la présente quittance n'emporte pas présomption de paiement des termes antérieurs. Sous réserve d'encaissement.
        </p>

        <div class="signature">
            <p><strong>L'Agence ESTATIQ</strong></p>
            <p style="color: #6b7280; font-style: italic;">Document généré numériquement</p>
        </div>
    </div>

</body>
</html>