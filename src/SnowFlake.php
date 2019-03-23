<?php
/**
 * Created by PhpStorm.
 * User: yiranzai
 * Date: 19-3-23
 * Time: 下午3:56
 */

namespace Yiranzai\SnowFlake;

/**
 * Class SnowFlake
 * @package Yiranzai\SnowFlake
 */
final class SnowFlake
{
    // 41bit timestamp + 5bit dataCenter + 5bit worker + 12bit
    public const LENGTH = 2 << 5;
    public const TIMESTAMP_LENGTH = 41;
    public const DATA_CENTER_LENGTH = 5;
    public const WORKER_LENGTH = 5;
    public const SEQUENCE_LENGTH = 12;

    public const WORKER_LEFT_SHIFT = 12;
    public const DATA_CENTER_LEFT_SHIFT = 17;
    public const TIMESTAMP_LEFT_SHIFT = 22;

    public const MAX_SEQUENCE = (2 << 11) - 1;

    /**
     * 最后一次的时间戳
     *
     * @var int
     */
    private static $lastTimestamp = 0;
    /**
     * @var int
     */
    private static $lastSequence = 0;
    /**
     * @var int
     */
    private static $baseTimestamp = 1553328109824;

    /**
     * @param int $dataCenterID
     * @param int $workerID
     * @return int
     */
    public static function next($dataCenterID = 0, $workerID = 0): int
    {

        $timestamp = self::generateTimestamp();

        if (self::$lastTimestamp === $timestamp) {
            ++self::$lastSequence;
            if (self::$lastSequence > self::MAX_SEQUENCE) {
                $timestamp          = self::nextMillisecond();
                self::$lastSequence = 0;
            }
        } else {
            self::$lastSequence = 0;
        }
        self::$lastTimestamp = $timestamp;

        $snowFlakeId = (($timestamp - self::$baseTimestamp) << self::TIMESTAMP_LEFT_SHIFT)
            | ($dataCenterID << self::DATA_CENTER_LEFT_SHIFT)
            | ($workerID << self::WORKER_LEFT_SHIFT)
            | self::$lastSequence;
        return $snowFlakeId;
    }

    /**
     * @param $snowFlakeId
     * @return Node
     */
    public static function analysis($snowFlakeId): Node
    {
        $Binary             = str_pad(decbin($snowFlakeId), self::LENGTH, '0', STR_PAD_LEFT);
        $node               = new Node();
        $node->timestamp    = bindec(substr($Binary, 0, self::TIMESTAMP_LENGTH))
            + self::$baseTimestamp;
        $node->dataCenterID = bindec(substr(
            $Binary,
            self::TIMESTAMP_LENGTH + 1,
            self::DATA_CENTER_LENGTH
        ));
        $node->workerID     = bindec(substr(
            $Binary,
            self::LENGTH - self::DATA_CENTER_LEFT_SHIFT,
            self::WORKER_LENGTH
        ));
        $node->sequence     = bindec(substr($Binary, -self::SEQUENCE_LENGTH));
        return $node;
    }

    /**
     * @return int
     */
    private static function nextMillisecond(): int
    {
        $timestamp = self::generateTimestamp();
        while ($timestamp <= self::$lastTimestamp) {
            $timestamp = self::generateTimestamp();
        }
        return $timestamp;
    }

    /**
     * @return int
     */
    private static function generateTimestamp(): int
    {
        return (int)floor(microtime(true) * 1000);
    }

    /**
     * just test phpunit
     *
     * @param int $dataCenterID
     * @param int $workerID
     * @return int
     */
    public static function test($dataCenterID = 0, $workerID = 0): int
    {
        $timestamp = self::generateTimestamp();

        if (self::$lastTimestamp === $timestamp) {
            ++self::$lastSequence;
            if (self::$lastSequence > 10) {
                $timestamp          = self::nextMillisecond();
                self::$lastSequence = 0;
            }
        } else {
            self::$lastSequence = 0;
        }
        self::$lastTimestamp = $timestamp;

        $snowFlakeId = (($timestamp - self::$baseTimestamp) << self::TIMESTAMP_LEFT_SHIFT)
            | ($dataCenterID << self::DATA_CENTER_LEFT_SHIFT)
            | ($workerID << self::WORKER_LEFT_SHIFT)
            | self::$lastSequence;
        return $snowFlakeId;
    }
}
