<?php

namespace BitWasp\Bitcoin\Test\Network\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Network\Messages\Ping;
use BitWasp\Bitcoin\Network\Messages\Pong;
use BitWasp\Bitcoin\Tests\AbstractTestCase;

class PongTest extends AbstractTestCase
{
    /**
     * @return array
     */
    public function generateSet()
    {
        $random = new Random();
        $set = [];
        for ($i = 0; $i < 2; $i++) {
            $set[] = [new Ping($random->bytes(8)->getInt())];
        }
        return $set;
    }

    /**
     * @dataProvider generateSet
     */
    public function testPong(Ping $ping)
    {
        $pong = new Pong($ping->getNonce());
        $this->assertEquals('pong', $pong->getNetworkCommand());
        $this->assertTrue($ping->getNonce() == $pong->getNonce());

        $math = $this->safeMath();
        $this->assertEquals(str_pad($math->decHex($ping->getNonce()), 16, '0', STR_PAD_LEFT), $pong->getHex());
    }
}
