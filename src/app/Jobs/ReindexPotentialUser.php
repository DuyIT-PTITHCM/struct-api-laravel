<?php


namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class ReindexPotentialUser extends Job implements ShouldBeUniqueUntilProcessing
{
    protected $updatedDate;

    public function __construct($updatedDate)
    {
        $this->queue = 'ReindexPotentialUser';
        $this->updatedDate = $updatedDate;
    }

    /**
     * Execute the job.
     *
     */
    public function handle()
    {

    }
}
