<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use App\Models\Tenant;
use App\Models\Owner;
use App\Models\Employee;
use App\Models\Invoice;
use Carbon\Carbon;

class ElectronicSignatureService
{
    protected string $provider;
    protected array $config;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $accountId;

    /**
     * Constructeur avec sélection du fournisseur
     */
    public function __construct(string $provider = 'docusign')
    {
        $this->provider = $provider;
        $this->config = config("services.{$provider}");
        
        if (!$this->config || !$this->config['enabled']) {
            throw new \Exception("Le fournisseur {$provider} n'est pas configuré ou désactivé");
        }

        $this->baseUrl = $this->config['base_url'];
        $this->apiKey = $this->config['api_key'];
        $this->accountId = $this->config['account_id'] ?? null;
    }

    /**
     * Crée une enveloppe de signature pour un contrat de bail
     */
    public function createLeaseContractEnvelope(Tenant $tenant, array $signers, Document $document): array
    {
        return match($this->provider) {
            'docusign' => $this->createDocuSignEnvelope($tenant, $signers, $document, 'lease_contract'),
            'dropbox_sign' => $this->createDropboxSignEnvelope($tenant, $signers, $document, 'lease_contract'),
            default => throw new \Exception("Fournisseur non supporté : {$this->provider}")
        };
    }

    /**
     * Crée une enveloppe de signature pour une facture
     */
    public function createInvoiceEnvelope(Invoice $invoice, array $signers, Document $document): array
    {
        return match($this->provider) {
            'docusign' => $this->createDocuSignEnvelope($invoice, $signers, $document, 'invoice'),
            'dropbox_sign' => $this->createDropboxSignEnvelope($invoice, $signers, $document, 'invoice'),
            default => throw new \Exception("Fournisseur non supporté : {$this->provider}")
        };
    }

    /**
     * Crée une enveloppe DocuSign
     */
    private function createDocuSignEnvelope($entity, array $signers, Document $document, string $type): array
    {
        $envelopeData = [
            'documents' => [
                [
                    'documentId' => '1',
                    'name' => $document->name,
                    'fileExtension' => 'pdf',
                    'documentBase64' => base64_encode(Storage::get($document->file_path))
                ]
            ],
            'recipients' => [
                'signers' => $this->formatDocuSignSigners($signers)
            ],
            'emailSubject' => $this->getEmailSubject($type, $entity),
            'emailBlurb' => $this->getEmailBody($type, $entity),
            'status' => 'sent'
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post("{$this->baseUrl}/restapi/v2.1/accounts/{$this->accountId}/envelopes", $envelopeData);

        if (!$response->successful()) {
            throw new \Exception("Erreur DocuSign: " . $response->body());
        }

        $envelope = $response->json();

        return [
            'envelope_id' => $envelope['envelopeId'],
            'status' => $envelope['status'],
            'created_date_time' => $envelope['createdDateTime'],
            'signing_url' => $this->getDocuSignSigningUrl($envelope['envelopeId']),
            'provider' => 'docusign'
        ];
    }

    /**
     * Crée une enveloppe Dropbox Sign
     */
    private function createDropboxSignEnvelope($entity, array $signers, Document $document, string $type): array
    {
        $signatureRequestData = [
            'title' => $this->getEmailSubject($type, $entity),
            'subject' => $this->getEmailSubject($type, $entity),
            'message' => $this->getEmailBody($type, $entity),
            'signers' => $this->formatDropboxSignSigners($signers),
            'file' => [
                [
                    'name' => $document->name,
                    'file' => base64_encode(Storage::get($document->file_path))
                ]
            ],
            'test_mode' => $this->config['test_mode'] ?? true,
            'client_id' => $this->config['client_id'] ?? null
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post("{$this->baseUrl}/signature_request/send", $signatureRequestData);

        if (!$response->successful()) {
            throw new \Exception("Erreur Dropbox Sign: " . $response->body());
        }

        $signatureRequest = $response->json();

        return [
            'envelope_id' => $signatureRequest['signature_request']['signature_request_id'],
            'status' => $signatureRequest['signature_request']['status_code'],
            'created_date_time' => $signatureRequest['signature_request']['created_at'],
            'signing_url' => $signatureRequest['signature_request']['signing_url'] ?? null,
            'provider' => 'dropbox_sign'
        ];
    }

    /**
     * Formate les signataires pour DocuSign
     */
    private function formatDocuSignSigners(array $signers): array
    {
        return array_map(function ($signer, $index) {
            return [
                'email' => $signer['email'],
                'name' => $signer['name'],
                'recipientId' => (string)($index + 1),
                'tabs' => [
                    'signHereTabs' => [
                        [
                            'documentId' => '1',
                            'pageNumber' => $signer['page'] ?? '1',
                            'xPosition' => $signer['x'] ?? '100',
                            'yPosition' => $signer['y'] ?? '100'
                        ]
                    ]
                ]
            ];
        }, $signers, array_keys($signers));
    }

    /**
     * Formate les signataires pour Dropbox Sign
     */
    private function formatDropboxSignSigners(array $signers): array
    {
        return array_map(function ($signer, $index) {
            return [
                'email_address' => $signer['email'],
                'name' => $signer['name'],
                'order' => $index + 1,
                'pin' => $signer['pin'] ?? null
            ];
        }, $signers, array_keys($signers));
    }

    /**
     * Obtient l'état d'une enveloppe
     */
    public function getEnvelopeStatus(string $envelopeId): array
    {
        return match($this->provider) {
            'docusign' => $this->getDocuSignEnvelopeStatus($envelopeId),
            'dropbox_sign' => $this->getDropboxSignEnvelopeStatus($envelopeId),
            default => throw new \Exception("Fournisseur non supporté : {$this->provider}")
        };
    }

    /**
     * Obtient le statut d'une enveloppe DocuSign
     */
    private function getDocuSignEnvelopeStatus(string $envelopeId): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json'
        ])->get("{$this->baseUrl}/restapi/v2.1/accounts/{$this->accountId}/envelopes/{$envelopeId}");

        if (!$response->successful()) {
            throw new \Exception("Erreur statut DocuSign: " . $response->body());
        }

        $envelope = $response->json();

        return [
            'envelope_id' => $envelope['envelopeId'],
            'status' => $envelope['status'],
            'completed_date_time' => $envelope['completedDateTime'] ?? null,
            'sent_date_time' => $envelope['sentDateTime'] ?? null,
            'recipients' => $envelope['recipients']['signers'] ?? []
        ];
    }

    /**
     * Obtient le statut d'une enveloppe Dropbox Sign
     */
    private function getDropboxSignEnvelopeStatus(string $envelopeId): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json'
        ])->get("{$this->baseUrl}/signature_request/{$envelopeId}");

        if (!$response->successful()) {
            throw new \Exception("Erreur statut Dropbox Sign: " . $response->body());
        }

        $signatureRequest = $response->json();

        return [
            'envelope_id' => $signatureRequest['signature_request']['signature_request_id'],
            'status' => $signatureRequest['signature_request']['status_code'],
            'title' => $signatureRequest['signature_request']['title'],
            'signatures' => $signatureRequest['signature_request']['signatures'] ?? []
        ];
    }

    /**
     * Obtient l'URL de signature pour un signataire
     */
    private function getDocuSignSigningUrl(string $envelopeId): ?string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post("{$this->baseUrl}/restapi/v2.1/accounts/{$this->accountId}/envelopes/{$envelopeId}/views/recipient", [
            'returnUrl' => config('app.url') . '/signature/completed',
            'authenticationMethod' => 'None',
            'email' => auth()->user()->email ?? 'user@example.com',
            'userName' => auth()->user()->name ?? 'Utilisateur'
        ]);

        if (!$response->successful()) {
            return null;
        }

        return $response->json()['url'] ?? null;
    }

    /**
     * Obtient l'objet d'email
     */
    private function getEmailSubject(string $type, $entity): string
    {
        return match($type) {
            'lease_contract' => 'Contrat de bail à signer - ' . config('app.name'),
            'invoice' => 'Facture à valider - ' . config('app.name'),
            'maintenance_contract' => 'Contrat de maintenance à signer - ' . config('app.name'),
            default => 'Document à signer - ' . config('app.name')
        };
    }

    /**
     * Obtient le corps de l'email
     */
    private function getEmailBody(string $type, $entity): string
    {
        return match($type) {
            'lease_contract' => 'Veuillez signer le contrat de bail ci-joint.',
            'invoice' => 'Veuillez valider la facture ci-jointe.',
            'maintenance_contract' => 'Veuillez signer le contrat de maintenance ci-joint.',
            default => 'Veuillez signer le document ci-joint.'
        };
    }

    /**
     * Télécharge le document signé
     */
    public function downloadSignedDocument(string $envelopeId): ?string
    {
        return match($this->provider) {
            'docusign' => $this->downloadDocuSignDocument($envelopeId),
            'dropbox_sign' => $this->downloadDropboxSignDocument($envelopeId),
            default => throw new \Exception("Fournisseur non supporté : {$this->provider}")
        };
    }

    /**
     * Télécharge le document DocuSign
     */
    private function downloadDocuSignDocument(string $envelopeId): ?string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/pdf'
        ])->get("{$this->baseUrl}/restapi/v2.1/accounts/{$this->accountId}/envelopes/{$envelopeId}/documents/combined");

        if (!$response->successful()) {
            return null;
        }

        $filename = "signed_document_{$envelopeId}_" . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf';
        $path = "signed_documents/{$filename}";
        
        Storage::put($path, $response->body());
        
        return $path;
    }

    /**
     * Télécharge le document Dropbox Sign
     */
    private function downloadDropboxSignDocument(string $envelopeId): ?string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey
        ])->get("{$this->baseUrl}/signature_request/files/{$envelopeId}");

        if (!$response->successful()) {
            return null;
        }

        $filename = "signed_document_{$envelopeId}_" . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf';
        $path = "signed_documents/{$filename}";
        
        Storage::put($path, $response->body());
        
        return $path;
    }

    /**
     * Obtient la liste des fournisseurs disponibles
     */
    public static function getAvailableProviders(): array
    {
        $providers = [];
        
        if (config('services.docusign.enabled')) {
            $providers['docusign'] = 'DocuSign';
        }
        
        if (config('services.dropbox_sign.enabled')) {
            $providers['dropbox_sign'] = 'Dropbox Sign';
        }
        
        return $providers;
    }
}