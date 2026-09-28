<?php

namespace Database\Seeders;

use App\Models\AutomotiveDetailing;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AutomotiveDetailingSeeder extends Seeder
{
    private const DETAILINGS = [
        ['Brilho Norte Estética Automotiva', 'Estética focada em cuidado de pintura e interior, com atendimento por agendamento e retirada do veículo no mesmo dia na maioria dos serviços.', 'Avenida Norte-Sul', '1480', null, 'Cambuí', '13025-320', -22.8962, -47.0521],
        ['Garagem Espelho Detailing', 'Especializada em polimento técnico e proteção cerâmica. Trabalhamos com produtos de pH neutro e iluminação específica para correção de pintura.', 'Rua Dr. Emílio Ribas', '742', 'Galpão 2', 'Cambuí', '13025-141', -22.8987, -47.0489],
        ['Lava & Cuida Taquaral', 'Lavagens completas e higienização interna perto da Lagoa do Taquaral. Espaço coberto e sala de espera com Wi-Fi.', 'Avenida Heitor Penteado', '1920', null, 'Taquaral', '13087-000', -22.8764, -47.0548],
        ['Estúdio Autocare Barão', 'Pequeno estúdio de estética automotiva em Barão Geraldo, atendimento próximo e acabamento cuidadoso.', 'Avenida Albino J. B. de Oliveira', '1105', 'Loja 3', 'Barão Geraldo', '13084-008', -22.8193, -47.0802],
        ['Oficina do Brilho', 'Do básico ao detalhado: lavagem, cera, higienização de bancos e revitalização de plásticos.', 'Rua Barão de Jaguara', '1320', null, 'Centro', '13015-002', -22.9056, -47.0608],
        ['Detalhe Fino Estética Veicular', 'Correção de pintura, vitrificação e cuidados com couro. Veículos entregues com relatório fotográfico do serviço.', 'Avenida José de Souza Campos', '2350', null, 'Nova Campinas', '13092-123', -22.8995, -47.0412],
        ['Ponto Limpo Auto Spa', 'Lavagem a seco e tradicional com foco em rapidez para o dia a dia, sem abrir mão do cuidado.', 'Rua Padre Almeida', '515', null, 'Cambuí', '13025-251', -22.8971, -47.0535],
        ['Castelo Car Care', 'Estética automotiva de bairro com atendimento familiar. Aceitamos carros, SUVs e utilitários leves.', 'Avenida Andrade Neves', '2011', null, 'Castelo', '13070-001', -22.8876, -47.0712],
        ['Refino Estética Automotiva', 'Tratamentos de pintura, faróis e vidros. Espaço climatizado e controle de poeira para aplicação de coatings.', 'Avenida Brasil', '1660', 'Fundos', 'Jardim Guanabara', '13073-001', -22.8838, -47.0765],
        ['Cuidado Total Auto Estética', 'Pacotes de manutenção periódica e higienização completa para quem usa o carro todos os dias.', 'Avenida John Boyd Dunlop', '3400', null, 'Jardim Ipaussurama', '13060-803', -22.9245, -47.1028],
    ];

    private const SERVICES = [
        ['Lavagem simples', 'Lavagem externa com shampoo neutro, secagem com microfibra, limpeza de rodas e aspiração rápida do interior.', 5000, 8000, 40],
        ['Lavagem detalhada', 'Lavagem externa minuciosa com pincéis em frisos e emblemas, limpeza de caixas de roda, aspiração completa e limpeza de painel.', 9000, 15000, 90],
        ['Lavagem de motor', 'Limpeza do cofre do motor com desengraxante apropriado e proteção de componentes elétricos, finalizada com revitalizador.', 8000, 13000, 60],
        ['Higienização interna', 'Extração de bancos e carpetes, limpeza de teto, portas e painel, com eliminação de odores.', 25000, 42000, 240],
        ['Hidratação de couro', 'Limpeza dos bancos de couro com produto específico e aplicação de hidratante para evitar ressecamento e rachaduras.', 15000, 28000, 120],
        ['Enceramento', 'Lavagem completa seguida de aplicação manual de cera de carnaúba para brilho e proteção por até 3 meses.', 12000, 20000, 90],
        ['Polimento técnico', 'Correção de pintura em etapas para remoção de riscos superficiais, marcas de lavagem e manchas, com acabamento de alto brilho.', 60000, 110000, 480],
        ['Vitrificação de pintura', 'Descontaminação, polimento de refino e aplicação de coating cerâmico com proteção de até 2 anos.', 120000, 250000, 720],
        ['Revitalização de faróis', 'Lixamento, polimento e aplicação de verniz UV nos faróis para recuperar transparência e iluminação.', 12000, 22000, 90],
        ['Descontaminação de pintura', 'Remoção de piche, resina e partículas ferrosas com clay bar e produtos específicos, deixando a pintura lisa ao toque.', 18000, 30000, 120],
        ['Cristalização de vidros', 'Limpeza profunda e aplicação de repelente de água nos vidros, melhorando a visibilidade em dias de chuva.', 8000, 15000, 60],
        ['Revitalização de plásticos', 'Limpeza e aplicação de revitalizador nos plásticos externos e internos, recuperando a cor original.', 7000, 12000, 60],
    ];

    public function run(): void
    {
        foreach (self::DETAILINGS as $index => [$name, $description, $street, $number, $complement, $neighborhood, $postalCode, $latitude, $longitude]) {
            $detailing = AutomotiveDetailing::factory()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $description,
                'phone' => sprintf('(19) 3%03d-%04d', 200 + $index * 37, 1000 + $index * 811),
                'whatsapp' => sprintf('(19) 9%04d-%04d', 8100 + $index * 53, 2000 + $index * 677),
                'email' => 'contato@'.Str::slug($name, '').'.com.br',
                'postal_code' => $postalCode,
                'street' => $street,
                'number' => $number,
                'complement' => $complement,
                'neighborhood' => $neighborhood,
                'city' => 'Campinas',
                'state' => 'SP',
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            $services = collect(self::SERVICES)->random(fake()->numberBetween(4, 10));

            foreach ($services as [$serviceName, $serviceDescription, $minPrice, $maxPrice, $duration]) {
                Service::factory()->for($detailing)->create([
                    'name' => $serviceName,
                    'description' => $serviceDescription,
                    'price_cents' => intdiv(fake()->numberBetween($minPrice, $maxPrice), 500) * 500,
                    'duration_minutes' => $duration,
                ]);
            }
        }
    }
}
