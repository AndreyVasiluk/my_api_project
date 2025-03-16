# Symfony Dataset Processing API

This project is a Symfony-based API that simulates processing a large dataset. The `/process-huge-dataset` endpoint returns a JSON array with at least 5 objects, each containing two fields. The response is cached for 1 minute, and a sleep command simulates a long-running process. A locking mechanism ensures only one process updates the cache at a time.

