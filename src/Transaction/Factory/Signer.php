<?php

namespace BitWasp\Bitcoin\Transaction\Factory;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\EcAdapter\Adapter\EcAdapterInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PrivateKeyInterface;
use BitWasp\Bitcoin\Script\ScriptInterface;
use BitWasp\Bitcoin\Transaction\SignatureHash\SigHash;
use BitWasp\Bitcoin\Transaction\TransactionFactory;
use BitWasp\Bitcoin\Transaction\TransactionInterface;
use BitWasp\Bitcoin\Transaction\TransactionOutputInterface;

class Signer
{
    /**
     * @var EcAdapterInterface
     */
    private $ecAdapter;

    /**
     * @var TransactionInterface
     */
    private $tx;

    /**
     * @var InputSigner[]
     */
    private $signatureCreator = [];

    /**
     * TxWitnessSigner constructor.
     * @param TransactionInterface $tx
     * @param EcAdapterInterface $ecAdapter
     */
    public function __construct(TransactionInterface $tx, EcAdapterInterface $ecAdapter = null)
    {
        $this->tx = $tx;
        $this->ecAdapter = $ecAdapter ?: Bitcoin::getEcAdapter();
    }

    /**
     * @param int $nIn
     * @param PrivateKeyInterface $key
     * @param TransactionOutputInterface $txOut
     * @param ScriptInterface|null $redeemScript
     * @param ScriptInterface|null $witnessScript
     * @param int $sigHashType
     * @return $this
     */
    public function sign($nIn, PrivateKeyInterface $key, TransactionOutputInterface $txOut, ScriptInterface $redeemScript = null, ScriptInterface $witnessScript = null, $sigHashType = SigHash::ALL)
    {
        $signData = new SignData();
        if ($redeemScript) {
            $signData->p2sh($redeemScript);
        }
        if ($witnessScript) {
            $signData->p2wsh($witnessScript);
        }

        if (!$this->signer($nIn, $txOut, $signData)->sign($key, $sigHashType)) {
            throw new \RuntimeException('Unsignable script');
        }

        return $this;
    }

    /**
     * @param int $nIn
     * @param TransactionOutputInterface $txOut
     * @param SignData $signData
     * @return InputSigner
     */
    public function signer($nIn, TransactionOutputInterface $txOut, SignData $signData)
    {
        if (!isset($this->signatureCreator[$nIn])) {
            $this->signatureCreator[$nIn] = new InputSigner($this->ecAdapter, $this->tx, $nIn, $txOut, $signData);
        }

        return $this->signatureCreator[$nIn];
    }

    /**
     * @return TransactionInterface
     */
    public function get()
    {
        $mutable = TransactionFactory::mutate($this->tx);
        $witnesses = [];
        foreach ($mutable->inputsMutator() as $idx => $input) {
            $sig = $this->signatureCreator[$idx]->serializeSignatures();
            $input->script($sig->getScriptSig());
            $witnesses[$idx] = $sig->getScriptWitness();
        }

        if (count($witnesses) > 0) {
            $mutable->witness($witnesses);
        }

        $new = $mutable->done();
        return $new;
    }
}