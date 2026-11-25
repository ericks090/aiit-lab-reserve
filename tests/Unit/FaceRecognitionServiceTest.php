<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\FaceRecognitionService;
use PHPUnit\Framework\TestCase;

class FaceRecognitionServiceTest extends TestCase
{
    private FaceRecognitionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FaceRecognitionService();
    }

    public function test_euclidean_distance_identical_embeddings_is_zero(): void
    {
        $v1 = array_fill(0, 128, 0.5);
        $v2 = array_fill(0, 128, 0.5);

        $distance = $this->service->calculateDistance($v1, $v2);
        $this->assertEquals(0.0, $distance);
    }

    public function test_euclidean_distance_known_vectors(): void
    {
        $v1 = [1.0, 2.0, 3.0];
        $v2 = [4.0, 6.0, 3.0];
        // (1-4)^2 + (2-6)^2 + (3-3)^2 = 9 + 16 + 0 = 25 -> sqrt(25) = 5.0
        $distance = $this->service->calculateDistance($v1, $v2);
        $this->assertEquals(5.0, $distance);
    }

    public function test_verify_face_matches_within_threshold(): void
    {
        $user = new User();
        // 128-D vector
        $base = array_fill(0, 128, 0.1);
        $user->face_embedding = $base;

        // Very slight variance -> distance ~0.02 < 0.45
        $live = $base;
        $live[0] += 0.02;

        $result = $this->service->verifyFace($user, $live, 0.45);
        $this->assertTrue($result['verified']);
        $this->assertLessThan(0.45, $result['distance']);
    }

    public function test_verify_face_rejects_different_face(): void
    {
        $user = new User();
        $base = array_fill(0, 128, 0.1);
        $user->face_embedding = $base;

        // Significant difference -> distance > 0.45
        $live = array_fill(0, 128, 0.6);

        $result = $this->service->verifyFace($user, $live, 0.45);
        $this->assertFalse($result['verified']);
        $this->assertGreaterThan(0.45, $result['distance']);
    }
}
