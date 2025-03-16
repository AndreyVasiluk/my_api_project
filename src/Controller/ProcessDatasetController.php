<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use OpenApi\Attributes as OA;

class ProcessDatasetController extends AbstractController
{
    private LockInterface $lock;

    public function __construct(private CacheInterface $cache, LockFactory $lockFactory)
    {
        $this->lock = $lockFactory->createLock('process_huge_dataset_lock');
    }

    #[Route('/process-huge-dataset', methods: ['GET'])]
    #[OA\Get(
    path: "/process-huge-dataset",
        summary: "Processes a huge dataset",
        description: "Simulates a long-running process and caches the result without using local storage.",
        tags: ["Dataset Processing"]
    )]
    #[OA\Response(
        response: 200,
        description: "Returns a JSON array of objects with at least 5 elements.",
        content: new OA\JsonContent(
    type: "array",
            items: new OA\Items(
    type: "object",
                properties: [
    new OA\Property(property: "id", type: "integer"),
                    new OA\Property(property: "value", type: "string")
                ]
            )
        )
    )]
    public function processDataset(): JsonResponse
    {
        return $this->cache->get('process_huge_dataset', function (ItemInterface $item) {
        $item->expiresAfter(60);

        $maxWait = 15;
        $startTime = time();


        while (!$this->lock->acquire() && (time() - $startTime) < $maxWait) {
            usleep(100_000);
            }


        if ($this->lock->isAcquired()) {
            try {
                sleep(10);
                $data = [
                    ['id' => 1, 'value' => 'Data A'],
                    ['id' => 2, 'value' => 'Data B'],
                    ['id' => 3, 'value' => 'Data C'],
                    ['id' => 4, 'value' => 'Data D'],
                    ['id' => 5, 'value' => 'Data E']
                ];
            } finally {
                $this->lock->release();
            }
        } else {
            $data = $this->cache->get('process_huge_dataset', fn() => []);
            }

        return new JsonResponse($data);
    });
    }
}