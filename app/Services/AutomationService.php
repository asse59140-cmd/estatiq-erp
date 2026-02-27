<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Owner;
use App\Models\Employee;
use App\Models\Agency;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AutomationService
{
    protected Agency $agency;
    protected array $settings;

    /**
     * Constructeur
     */
    public function __construct(Agency $agency)
    {
        $this->agency = $agency;
        $this->settings = config("services.automation.{$agency->id}", []);
    }

    /**
     * Envoie automatiquement les quittances par WhatsApp et Email
     */
    public function sendMonthlyReceipts(Carbon $month): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        // Récupérer tous les paiements du mois
        $payments = Payment::with(['tenant', 'tenant.unit'])
            ->where('agency_id', $this->agency->id)
            ->whereMonth('payment_date', $month->month)
            ->whereYear('payment_date', $month->year)
            ->where('status', 'completed')
            ->get();

        foreach ($payments as $payment) {
            try {
                $result = $this->sendReceiptForPayment($payment);
                
                if ($result['success']) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'payment_id' => $payment->id,
                        'error' => $result['error']
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ];
                Log::error("Erreur envoi quittance paiement {$payment->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Envoie une quittance pour un paiement spécifique
     */
    private function sendReceiptForPayment(Payment $payment): array
    {
        $tenant = $payment->tenant;
        
        if (!$tenant || !$tenant->email) {
            return ['success' => false, 'error' => 'Locataire sans email'];
        }

        // Générer le PDF de la quittance
        $pdfPath = $this->generateReceiptPDF($payment);
        
        if (!$pdfPath) {
            return ['success' => false, 'error' => 'Erreur génération PDF'];
        }

        // Envoyer par email
        $emailResult = $this->sendReceiptByEmail($payment, $pdfPath);
        
        // Envoyer par WhatsApp si configuré
        $whatsappResult = $this->sendReceiptByWhatsApp($payment, $pdfPath);

        // Nettoyer le fichier temporaire
        if (Storage::exists($pdfPath)) {
            Storage::delete($pdfPath);
        }

        return [
            'success' => $emailResult['success'] || $whatsappResult['success'],
            'email_sent' => $emailResult['success'],
            'whatsapp_sent' => $whatsappResult['success'],
            'errors' => array_filter([$emailResult['error'] ?? null, $whatsappResult['error'] ?? null])
        ];
    }

    /**
     * Génère le PDF de la quittance
     */
    private function generateReceiptPDF(Payment $payment): ?string
    {
        try {
            $html = view('pdf.receipt', ['payment' => $payment])->render();
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $filename = "quittance_{$payment->id}_" . Carbon::now()->format('Y-m-d') . '.pdf';
            $path = "temp/{$filename}";
            
            Storage::put($path, $pdf->output());
            
            return $path;
        } catch (\Exception $e) {
            Log::error("Erreur génération PDF quittance: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Envoie la quittance par email
     */
    private function sendReceiptByEmail(Payment $payment, string $pdfPath): array
    {
        try {
            $tenant = $payment->tenant;
            $agency = $this->agency;
            
            $data = [
                'tenant' => $tenant,
                'payment' => $payment,
                'agency' => $agency,
                'month' => Carbon::parse($payment->payment_date)->format('F Y')
            ];

            Mail::send('emails.receipt', $data, function ($message) use ($tenant, $pdfPath, $agency) {
                $message->to($tenant->email)
                        ->subject("Quittance de loyer - {$agency->name}")
                        ->attach(Storage::path($pdfPath), [
                            'as' => 'quittance.pdf',
                            'mime' => 'application/pdf',
                        ]);
            });

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error("Erreur envoi email quittance: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envoie la quittance par WhatsApp
     */
    private function sendReceiptByWhatsApp(Payment $payment, string $pdfPath): array
    {
        if (!$this->isWhatsAppConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp non configuré'];
        }

        $tenant = $payment->tenant;
        
        if (!$tenant->phone) {
            return ['success' => false, 'error' => 'Locataire sans numéro WhatsApp'];
        }

        try {
            $message = $this->formatWhatsAppReceiptMessage($payment);
            
            // Envoyer le message texte
            $textResult = $this->sendWhatsAppMessage($tenant->phone, $message);
            
            // Envoyer le PDF
            $pdfResult = $this->sendWhatsAppDocument($tenant->phone, $pdfPath, "Quittance loyer");

            return [
                'success' => $textResult['success'] || $pdfResult['success'],
                'text_sent' => $textResult['success'],
                'pdf_sent' => $pdfResult['success']
            ];
        } catch (\Exception $e) {
            Log::error("Erreur envoi WhatsApp quittance: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Formate le message WhatsApp pour la quittance
     */
    private function formatWhatsAppReceiptMessage(Payment $payment): string
    {
        $tenant = $payment->tenant;
        $unit = $tenant->unit;
        $building = $unit->building ?? null;
        $month = Carbon::parse($payment->payment_date)->format('F Y');

        $message = "Bonjour {$tenant->full_name},\n\n";
        $message .= "Votre quittance de loyer pour le mois de *{$month}* est prête.\n\n";
        $message .= "📍 *Propriété*: " . ($building ? $building->name : 'N/A') . "\n";
        $message .= "🏠 *Unité*: " . ($unit ? $unit->unit_number : 'N/A') . "\n";
        $message .= "💰 *Montant*: " . number_format($payment->amount, 2, ',', ' ') . " €\n";
        $message .= "📅 *Date de paiement*: " . Carbon::parse($payment->payment_date)->format('d/m/Y') . "\n";
        $message .= "✅ *Statut*: Payé\n\n";
        $message .= "Merci pour votre confiance.\n";
        $message .= "*{$this->agency->name}*";

        return $message;
    }

    /**
     * Envoie un message WhatsApp
     */
    private function sendWhatsAppMessage(string $phone, string $message): array
    {
        $apiKey = $this->settings['whatsapp_api_key'] ?? null;
        $apiUrl = $this->settings['whatsapp_api_url'] ?? null;

        if (!$apiKey || !$apiUrl) {
            return ['success' => false, 'error' => 'Configuration WhatsApp manquante'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post($apiUrl . '/messages', [
                'phone' => $this->formatPhoneNumber($phone),
                'message' => $message
            ]);

            if ($response->successful()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Erreur API WhatsApp'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envoie un document WhatsApp
     */
    private function sendWhatsAppDocument(string $phone, string $documentPath, string $caption): array
    {
        $apiKey = $this->settings['whatsapp_api_key'] ?? null;
        $apiUrl = $this->settings['whatsapp_api_url'] ?? null;

        if (!$apiKey || !$apiUrl) {
            return ['success' => false, 'error' => 'Configuration WhatsApp manquante'];
        }

        if (!Storage::exists($documentPath)) {
            return ['success' => false, 'error' => 'Document non trouvé'];
        }

        try {
            $documentContent = Storage::get($documentPath);
            $base64Document = base64_encode($documentContent);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post($apiUrl . '/documents', [
                'phone' => $this->formatPhoneNumber($phone),
                'document' => $base64Document,
                'filename' => basename($documentPath),
                'caption' => $caption
            ]);

            if ($response->successful()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Erreur API WhatsApp document'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Formate le numéro de téléphone pour WhatsApp
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Supprimer les espaces et caractères spéciaux
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Ajouter le code pays si nécessaire (France)
        if (!str_starts_with($phone, '+')) {
            if (str_starts_with($phone, '0')) {
                $phone = '+33' . substr($phone, 1);
            } elseif (str_starts_with($phone, '33')) {
                $phone = '+' . $phone;
            } else {
                $phone = '+33' . $phone;
            }
        }

        return $phone;
    }

    /**
     * Vérifie si WhatsApp est configuré
     */
    private function isWhatsAppConfigured(): bool
    {
        return !empty($this->settings['whatsapp_api_key']) && 
               !empty($this->settings['whatsapp_api_url']);
    }

    /**
     * Envoie des rappels de paiement automatiques
     */
    public function sendPaymentReminders(): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        // Récupérer les factures impayées
        $overdueInvoices = Invoice::with(['client'])
            ->where('agency_id', $this->agency->id)
            ->where('status', 'overdue')
            ->where(function ($query) {
                $query->whereNull('reminder_sent_at')
                      ->orWhere('reminder_sent_at', '<', now()->subDays(3));
            })
            ->get();

        foreach ($overdueInvoices as $invoice) {
            try {
                $result = $this->sendPaymentReminder($invoice);
                
                if ($result['success']) {
                    $results['sent']++;
                    $invoice->update(['reminder_sent_at' => now()]);
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'invoice_id' => $invoice->id,
                        'error' => $result['error']
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Envoie un rappel de paiement pour une facture
     */
    private function sendPaymentReminder(Invoice $invoice): array
    {
        $client = $invoice->client;
        
        if (!$client || !$client->email) {
            return ['success' => false, 'error' => 'Client sans email'];
        }

        try {
            // Envoyer par email
            $emailResult = $this->sendPaymentReminderByEmail($invoice);
            
            // Envoyer par WhatsApp si configuré
            $whatsappResult = $this->sendPaymentReminderByWhatsApp($invoice);

            return [
                'success' => $emailResult['success'] || $whatsappResult['success'],
                'email_sent' => $emailResult['success'],
                'whatsapp_sent' => $whatsappResult['success']
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envoie un rappel de paiement par email
     */
    private function sendPaymentReminderByEmail(Invoice $invoice): array
    {
        try {
            $client = $invoice->client;
            $agency = $this->agency;
            
            $data = [
                'client' => $client,
                'invoice' => $invoice,
                'agency' => $agency,
                'days_overdue' => $invoice->days_overdue
            ];

            Mail::send('emails.payment_reminder', $data, function ($message) use ($client, $invoice, $agency) {
                $message->to($client->email)
                        ->subject("Rappel de paiement - Facture {$invoice->invoice_number}")
                        ->from($agency->email ?? 'noreply@estatiq.com', $agency->name);
            });

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envoie un rappel de paiement par WhatsApp
     */
    private function sendPaymentReminderByWhatsApp(Invoice $invoice): array
    {
        if (!$this->isWhatsAppConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp non configuré'];
        }

        $client = $invoice->client;
        
        if (!$client->phone) {
            return ['success' => false, 'error' => 'Client sans numéro WhatsApp'];
        }

        try {
            $message = $this->formatWhatsAppPaymentReminderMessage($invoice);
            
            return $this->sendWhatsAppMessage($client->phone, $message);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Formate le message WhatsApp pour le rappel de paiement
     */
    private function formatWhatsAppPaymentReminderMessage(Invoice $invoice): string
    {
        $client = $invoice->client;
        $daysOverdue = $invoice->days_overdue;
        $month = Carbon::parse($invoice->issue_date)->format('F Y');

        $message = "Bonjour {$client->full_name ?? 'Cher client'},\n\n";
        $message .= "⚠️ *Rappel de paiement* ⚠️\n\n";
        $message .= "Votre facture de loyer pour le mois de *{$month}* est en retard.\n\n";
        $message .= "📄 *Facture*: {$invoice->invoice_number}\n";
        $message .= "💰 *Montant dû*: " . number_format($invoice->balance_due, 2, ',', ' ') . " €\n";
        $message .= "📅 *Jours de retard*: {$daysOverdue}\n\n";
        
        if ($daysOverdue > 7) {
            $message .= "🚨 *Action requise*: Veuillez effectuer le paiement dans les plus brefs délais.\n\n";
        } else {
            $message .= "💡 *Solution*: Vous pouvez payer en ligne via notre portail client.\n\n";
        }
        
        $message .= "Pour tout question, n\'hésitez pas à nous contacter.\n";
        $message .= "Merci de votre compréhension.\n\n";
        $message .= "*{$this->agency->name}*";

        return $message;
    }
}