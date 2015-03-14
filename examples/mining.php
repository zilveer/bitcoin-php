<?php

use Afk11\Bitcoin\Bitcoin;
use Afk11\Bitcoin\Script\ScriptFactory;
use Afk11\Bitcoin\Key\PrivateKeyFactory;
use Afk11\Bitcoin\Rpc\RpcFactory;
use Afk11\Bitcoin\Miner\Miner;
use Afk11\Bitcoin\Network\Network;

require __DIR__ . "/../vendor/autoload.php";

// init network (TESTNET)
$network = new Network('6f', 'c4', 'ef', true);

// generate a privatekey so we can received the BTC
$privKey = PrivateKeyFactory::create(true);
var_dump($privKey->toWif($network));

// get latest block from RPC
$rpc = RpcFactory::bitcoind(
    getenv('BITCOINLIB_RPC_HOST') ?: 'localhost', 
    "18332",
    getenv('BITCOINLIB_RPC_USER') ?: 'bitcoin', 
    getenv('BITCOINLIB_RPC_PASSWORD') ?: 'YOUR_PASSWORD'
);

$latest = $rpc->getblock($rpc->getbestblockhash(), false);

// mining in the future \o/
$timestamp = time() + (3600 * 2);

// create script to pay ourselves
$script = ScriptFactory::payToPubKey($privKey->getPublicKey());

// init miner
$miner = new Miner(Bitcoin::getMath(), $latest->getHeader(), $script, null, $timestamp, 2, true);

// let's GO!
var_dump("mining!");
$block = $miner->run();

// result
var_dump($block->getHeader()->getBlockHash());
var_dump($block->serialize('hex'));