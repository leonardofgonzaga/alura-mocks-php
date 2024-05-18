<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use DateTimeImmutable;
use Alura\Leilao\Model\Leilao;
use PHPUnit\Framework\TestCase;
use Alura\Leilao\Service\Encerrador;

class LeilaoDaoMock extends LeilaoDao 
{
    private $leiloes = [];

    public function salva(Leilao $leilao): void
    {
        $this->leiloes[] = $leilao;
    }

    public function recuperarNaoFinalizados(): array
    {
        return array_filter($this->leiloes, function(Leilao $leilao) {
            return !$leilao->estaFinalizado();
        });
    }

    public function recuperarFinalizados(): array
    {
        return array_filter($this->leiloes, function(Leilao $leilao) {
            return $leilao->estaFinalizado();
        });
    }

    public function atualiza(Leilao $leilao): void
    {

    }
}

class EncerradorTest extends TestCase
{
    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $fiat147 = new Leilao(
            'Fiat 147 0Km',
            new DateTimeImmutable('8 years ago')
        );

        $variant = new Leilao(
            'Variant 1972 0Km',
            new DateTimeImmutable('10 years ago')
        );

        $leilaoDao = new LeilaoDaoMock();
        $leilaoDao->salva($fiat147);
        $leilaoDao->salva($variant);

        $encerrador = new Encerrador($leilaoDao);
        $encerrador->encerra();

        $leiloes = $leilaoDao->recuperarFinalizados();
        self::assertCount(2, $leiloes);
        self::assertEquals(
            'Fiat 147 0Km',
            $leiloes[0]->recuperarDescricao()
        );
        self::assertEquals(
            'Variant 1972 0Km',
            $leiloes[1]->recuperarDescricao()
        );
    }
}