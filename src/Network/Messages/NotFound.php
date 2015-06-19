<?php

namespace BitWasp\Bitcoin\Network\Messages;

use BitWasp\Bitcoin\Network\NetworkSerializable;
use BitWasp\Bitcoin\Network\Structure\InventoryVector;
use BitWasp\Bitcoin\Serializer\Network\Message\NotFoundSerializer;
use BitWasp\Bitcoin\Serializer\Network\Structure\InventoryVectorSerializer;
use BitWasp\Buffertools\Parser;

class NotFound extends NetworkSerializable implements \Countable
{
    /**
     * @var InventoryVector[]
     */
    private $vectors = [];

    /**
     * @param InventoryVector[] $vectors
     */
    public function __construct(array $vectors = [])
    {
        foreach ($vectors as $vector) {
            $this->addItem($vector);
        }
    }

    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return 'notfound';
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->vectors);
    }

    /**
     * @return \BitWasp\Bitcoin\Network\Structure\InventoryVector[]
     */
    public function getItems()
    {
        return $this->vectors;
    }

    /**
     * @param $index
     * @return InventoryVector
     */
    public function getItem($index)
    {
        if (false === isset($this->vectors[$index])) {
            throw new \InvalidArgumentException('No item found at that index');
        }

        return $this->vectors[$index];
    }

    /**
     * @param InventoryVector $vector
     * @return $this
     */
    public function addItem(InventoryVector $vector)
    {
        $this->vectors[] = $vector;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer()
    {
        return (new NotFoundSerializer(new InventoryVectorSerializer()))->serialize($this);
    }
}
