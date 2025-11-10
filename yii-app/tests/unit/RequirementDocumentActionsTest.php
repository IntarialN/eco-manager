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
                'review_mode' => \app\models\Document::REVIEW_MODE_AUDIT,
            ],
        ];

        $this->runControllerAction('requirement', 'upload-document', ['id' => 1], $post, $_FILES);

        $documents = Document::find()
            ->where(['requirement_id' => 1])
            ->asArray()
            ->all();
        fwrite(STDERR, print_r($documents, true));
        fwrite(STDERR, "Flash error: " . (Yii::$app->session->getFlash('error', null, true) ?? 'none') . PHP_EOL);
        $document = Document::find()
            ->where(['requirement_id' => 1])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        self::assertInstanceOf(Document::class, $document);
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

        $this->runControllerAction('requirement', 'approve-document', ['id' => $documentId]);

        $document = Document::findOne($documentId);
        self::assertInstanceOf(Document::class, $document);
        self::assertSame(Document::STATUS_APPROVED, $document->status);
        self::assertSame(1, $document->auditor_id);
        self::assertNotNull($document->audit_completed_at);
    }

    public function testRejectDocumentUpdatesStatus(): void
    {
        $documentId = $this->createDocumentRecord(Document::STATUS_PENDING);

        $this->runControllerAction('requirement', 'reject-document', ['id' => $documentId]);

        $document = Document::findOne($documentId);
        self::assertInstanceOf(Document::class, $document);
        self::assertSame(Document::STATUS_REJECTED, $document->status);
        self::assertSame(1, $document->auditor_id);
        self::assertNotNull($document->audit_completed_at);
    }

    public function testUploadDocumentWithoutAuditAutoApproved(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($tempFile, 'test');

        $_FILES = [
            'DynamicModel' => [
                'name' => ['file' => 'journal.pdf'],
                'type' => ['file' => 'application/pdf'],
                'tmp_name' => ['file' => $tempFile],
                'error' => ['file' => UPLOAD_ERR_OK],
                'size' => ['file' => filesize($tempFile)],
            ],
        ];

        $post = [
            'DynamicModel' => [
                'title' => 'Журнал',
                'type' => 'journal',
                'review_mode' => \app\models\Document::REVIEW_MODE_STORAGE,
            ],
        ];

        $this->runControllerAction('requirement', 'upload-document', ['id' => 1], $post, $_FILES);

        $document = Document::find()
            ->where(['requirement_id' => 1])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        self::assertInstanceOf(Document::class, $document);
        self::assertSame(Document::STATUS_APPROVED, $document->status);
        self::assertSame(Document::REVIEW_MODE_STORAGE, $document->review_mode);
        self::assertNull($document->auditor_id);

        $storedPath = Yii::getAlias('@app/web/uploads') . DIRECTORY_SEPARATOR . basename($document->path);
        self::assertFileExists($storedPath);
        unlink($storedPath);
    }

    private function createDocumentRecord(string $status): int
    {
        $document = new Document([
            'client_id' => 1,
            'requirement_id' => 1,
            'title' => 'Документ',
            'type' => 'report',
            'status' => $status,
            'review_mode' => Document::REVIEW_MODE_AUDIT,
            'path' => '/uploads/sample.pdf',
            'uploaded_at' => date('Y-m-d H:i:s'),
        ]);
        $document->save(false);

        return (int)$document->id;
    }
}
