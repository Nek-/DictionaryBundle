<?php

namespace Knp\DictionaryBundle\Dictionary;


use Knp\DictionaryBundle\DataCollector\DictionaryDataCollector;

trait TraceableDictionaryTrait
{
    /**
     * @var DictionaryDataCollector
     */
    private $collector;

    public function __construct(DictionaryDataCollector $collector, ...$args)
    {
        $this->collector = $collector;
        parent::__construct(...$args);
    }

    /**
     * Register this dictioanry as used.
     */
    private function trace()
    {
        $this->collector->addDictionary(
            $this->getName(),
            $this->getKeys(),
            \array_values($this->getValues())
        );
    }
}
