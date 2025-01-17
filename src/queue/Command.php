<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\queue;

use craft\helpers\Console;
use yii\console\ExitCode;

/**
 * Manages the queue
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @since 3.0
 */
class Command extends \yii\queue\cli\Command
{
    // Properties
    // =========================================================================

    /**
     * @var Queue
     */
    public $queue;

    /**
     * @var string
     */
    public $defaultAction = 'info';

    /**
     * @inheritdoc
     */
    public $verboseConfig = [
        'class' => VerboseBehavior::class,
    ];

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function isWorkerAction($actionID)
    {
        return in_array($actionID, ['run', 'listen'], true);
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'info' => InfoAction::class,
        ];
    }

    /**
     * Runs all jobs in the queue.
     *
     * @return int
     */
    public function actionRun(): int
    {
        $this->queue->run();
        return ExitCode::OK;
    }

    /**
     * Listens for new jobs added to the queue and runs them
     *
     * @param int $delay Number of seconds for waiting new job
     */
    public function actionListen($delay = 3)
    {
        $this->queue->listen($delay);
    }

    /**
     * Re-adds a failed job(s) to the queue.
     *
     * @param int|string $job The job ID that should be retried, or pass `all` to retry all failed jobs
     * @return int
     * @since 3.1.21
     */
    public function actionRetry($job): int
    {
        if (strtolower($job) === 'all') {
            $total = $this->queue->getTotalFailed();
            if ($total === 0) {
                $this->stdout('No failed jobs in the queue.' . PHP_EOL);
                return ExitCode::OK;
            }
            $this->stdout("Re-adding {$total} failed " . ($total === 1 ? 'job' : 'jobs') . ' back into the queue ... ');
            $this->queue->retryAll();
        } else {
            $this->stdout('Re-adding 1 failed job back into the queue ... ');
            $this->queue->retry($job);
        }

        $this->stdout('done' . PHP_EOL, Console::FG_GREEN);
        return ExitCode::OK;
    }
}
