<?php

declare(strict_types=1);

namespace tests\unit;

use app\models\Document;
use Yii;
use yii\web\UploadedFile;

final class RequirementDocumentActionsTest extends ControllerTestCase
{
    public function testUploadDocumentCreatesPendingRecord(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'upload');
        $pdfStub = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Count 0 >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        file_put_contents($tempFile, $pdfStub);

        $files = [
            'DynamicModel' => [
                'name' => ['file' => 'report.pdf'],
                'type' => ['file' => 'application/pdf'],
                'tmp_name' => ['file' => $tempFile],
                'error' => ['file' => UPLOAD_ERR_OK],
                'size' => ['file' => filesize($tempFile)],
            ],
        ];

        $post = [
            'DynamicModel' => [
                'title' => 'Акт проверки',
                'type' => 'report',
            ],
        ];

        UploadedFile::reset();
        $this->runControllerAction('requirement', 'upload-document', ['id' => 1], $post, $files);

        $document = Document::find()->where(['requirement_id' => 1])->one();
        if (!$document) {
            $error = Yii::$app->session->getFlash('error', null, false);
            self::fail('Document not created. Flash error: ' . ($error ?? 'none'));
        }
        self::assertSame(Document::STATUS_PENDING, $document->status);
        self::assertSame('Акт проверки', $document->title);
        self::assertSame('report', $document->type);
        self::assertNotNull($document->uploaded_at);
        self::assertMatchesRegularExpression('#^/uploads/req_1_.*\.pdf$#', $document->path);

        @unlink($tempFile);
    }

    public function testApproveDocumentUpdatesStatus(): void
    {
        $documentId = $this->createDocumentRecord(Document::STATUS_PENDING);

        UploadedFile::reset();
        $this->runControllerAction('requirement', 'approve-document', ['id' => $documentId]);

        $document = Document::findOne($documentId);
        self::assertInstanceOf(Document::class, $document);
        self::assertSame(Document::STATUS_APPROVED, $document->status);
    }

    public function testRejectDocumentUpdatesStatus(): void
    {
        $documentId = $this->createDocumentRecord(Document::STATUS_PENDING);

        UploadedFile::reset();
        $this->runControllerAction('requirement', 'reject-document', ['id' => $documentId]);

        $document = Document::findOne($documentId);
        self::assertInstanceOf(Document::class, $document);
        self::assertSame(Document::STATUS_REJECTED, $document->status);
    }

    private function createDocumentRecord(string $status): int
    {
        $document = new Document([
            'client_id' => 1,
            'requirement_id' => 1,
            'title' => 'Документ',
            'type' => 'report',
            'status' => $status,
            'path' => '/uploads/sample.pdf',
            'uploaded_at' => date('Y-m-d H:i:s'),
        ]);
        $document->save(false);

        return (int)$document->id;
    }
}
