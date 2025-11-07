<?php

declare(strict_types=1);

namespace tests\unit;

use app\models\Document;
use Yii;

final class RequirementDocumentActionsTest extends ControllerTestCase
{
    public function testUploadDocumentCreatesPendingRecord(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($tempFile, 'test');

        $_FILES = [
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

        $this->runControllerAction('requirement', 'upload-document', ['id' => 1], $post, $_FILES);

        $document = Document::find()->where(['requirement_id' => 1])->one();
        self::assertInstanceOf(Document::class, $document);
        self::assertSame(Document::STATUS_PENDING, $document->status);
        self::assertSame('Акт проверки', $document->title);
        self::assertSame('report', $document->type);
        self::assertNotNull($document->uploaded_at);
        self::assertMatchesRegularExpression('#^/uploads/req_1_.*\.pdf$#', $document->path);

        $storedPath = Yii::getAlias('@app/web/uploads') . DIRECTORY_SEPARATOR . basename($document->path);
        self::assertFileExists($storedPath);

        unlink($storedPath);
    }

    public function testApproveDocumentUpdatesStatus(): void
    {
        $documentId = $this->createDocumentRecord(Document::STATUS_PENDING);

        $this->runControllerAction('requirement', 'approve-document', ['id' => $documentId]);

        $document = Document::findOne($documentId);
        self::assertInstanceOf(Document::class, $document);
        self::assertSame(Document::STATUS_APPROVED, $document->status);
    }

    public function testRejectDocumentUpdatesStatus(): void
    {
        $documentId = $this->createDocumentRecord(Document::STATUS_PENDING);

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
