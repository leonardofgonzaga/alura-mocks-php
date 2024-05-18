<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use DateTimeImmutable;
use Alura\Leilao\Model\Leilao;
use PHPUnit\Framework\TestCase;
use Alura\Leilao\Service\Encerrador;
use PDO;

// class LeilaoDaoMock extends LeilaoDao 
// {
//     private $leiloes = [];

//     public function salva(Leilao $leilao): void
//     {
//         $this->leiloes[] = $leilao;
//     }

//     public function recuperarNaoFinalizados(): array
//     {
//         return array_filter($this->leiloes, function(Leilao $leilao) {
//             return !$leilao->estaFinalizado();
//         });
//     }

//     public function recuperarFinalizados(): array
//     {
//         return array_filter($this->leiloes, function(Leilao $leilao) {
//             return $leilao->estaFinalizado();
//         });
//     }

//     public function atualiza(Leilao $leilao): void
//     {

//     }
// }

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

        $leilaoDao = $this->createMock(LeilaoDao::class);

        // $leilaoDao = $this->getMockBuilder(LeilaoDao::class)
        //     ->disableOriginalConstructor()
        //     ->setConstructorArgs([new PDO('sqlite::memory:')])
        //     ->getMock();

        $leilaoDao->method('recuperarNaoFinalizados')
            ->willReturn([$fiat147, $variant]);
        $leilaoDao->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive(
                [$fiat147],
                [$variant]
            );

        $encerrador = new Encerrador($leilaoDao);
        $encerrador->encerra();

        $leiloes = [$fiat147, $variant];
        self::assertCount(2, $leiloes);
        self::assertTrue($leiloes[0]->estaFinalizado());
        self::assertTrue($leiloes[1]->estaFinalizado());
    }
}
