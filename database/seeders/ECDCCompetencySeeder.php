<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ECDCCompetencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $competencies = [
            0 => [
                'domain_id' => 1,
                'competency' => 'Nakaakyat ng silya o matataas na mga gamit gaya ng kama na walang tumutulong.',
            ],
            1 => [
                'domain_id' => 1,
                'competency' => 'Nakalalakad nang pabalik.',
            ],
            2 => [
                'domain_id' => 1,
                'competency' => 'Nakatatakbo nang hindi nadadapa. ',
            ],
            3 => [
                'domain_id' => 1,
                'competency' => 'Nakababa ng hagdan gamit ang parehong paa sa bawat baitang habang nakahawak sa gabay ng hagdan ang isang kamay.',
            ],
            4 => [
                'domain_id' => 1,
                'competency' => 'Nakaaakyat ng hagdan gamit ang parehong paa sa bawat baitang habang nakahawak sa gabay ng hagdan.',
            ],
            5 => [
                'domain_id' => 1,
                'competency' => 'Nakaaakyat ng hagdan na salitan ang mga paa na hindi humahawak sa gabay ng hagdan.',
            ],
            6 => [
                'domain_id' => 1,
                'competency' => 'Nakababa ng hagdan na salitan ang mga paa na  hindi humahawak sa gabay ng hagdan.',
            ],
            7 => [
                'domain_id' => 1,
                'competency' => 'Naigagalaw ang mga parte ng katawan kapag inutusan.',
            ],
            8 => [
                'domain_id' => 1,
                'competency' => 'Nakatatalon.',
            ],
            9 => [
                'domain_id' => 1,
                'competency' => 'Naihahagis ang bola sa paitaas na direksyon.',
            ],
            10 => [
                'domain_id' => 1,
                'competency' => 'Nakalulundag ng 1 – 3 beses gamit ang mas gustong paa.',
            ],
            11 => [
                'domain_id' => 1,
                'competency' => 'Tumatalon at umiikot.',
            ],
            12 => [
                'domain_id' => 1,
                'competency' => 'Nakasasayaw / nakasusunod sa mga hakbang ng sayaw, grupong gawain ayon sa kilos at galaw.',
            ],
            13 => [
                'domain_id' => 2,
                'competency' => 'Nagagamit ang limang  daliri sa pagkuha ng pagkain,bagay mula sa patag na lugar.',
            ],
            14 => [
                'domain_id' => 2,
                'competency' => 'Nakukuha ang mga bagay gamit ang hinlalaki at hintututro.',
            ],
            15 => [
                'domain_id' => 2,
                'competency' => 'Nagpapakita ng higit na pagkagusto sa paggamit ng particular na kamay.',
            ],
            16 => [
                'domain_id' => 2,
                'competency' => 'Inilalagay / inaalis ang maliliit na bagay sa lalagyan ',
            ],
            17 => [
                'domain_id' => 2,
                'competency' => 'Nahahawakan ang krayola gamit nang nakasara ang palad.',
            ],
            18 => [
                'domain_id' => 2,
                'competency' => 'Natatanggal ang takip ng bote/lalagyan, inaalis ang balot ng pagkain.',
            ],
            19 => [
                'domain_id' => 2,
                'competency' => 'Nakaguguhit nang mabilis na di maintindihang anyo.',
            ],
            20 => [
                'domain_id' => 2,
                'competency' => 'Nakaguguhit nang bilog na hugis.',
            ],
            21 => [
                'domain_id' => 2,
                'competency' => 'Nakaguguhit nang patayo at pahalang na guhit.',
            ],
            22 => [
                'domain_id' => 2,
                'competency' => 'Nakaguguhit ng larawan ng tao (ulo, mata, katawan, braso, kamay/daliri).',
            ],
            23 => [
                'domain_id' => 2,
                'competency' => 'Nakaguguhit ng bahay gamit ang iba’t-ibang uri ng hugis .',
            ],
            24 => [
                'domain_id' => 3,
                'competency' => 'Nakakakain nang mag-isa tulad ng biskwit at tinapay (finger food).',
            ],
            25 => [
                'domain_id' => 3,
                'competency' => 'Nakakakain nang mag-isa ng kanin at ulam gamit ang daliri ngunit may natatapong pagkain.',
            ],
            26 => [
                'domain_id' => 3,
                'competency' => 'Nakakakain nang mag-isa gamit ang kutsara ngunit may natatapong pagkain',
            ],
            27 => [
                'domain_id' => 3,
                'competency' => 'Nakakakain nang mag-isa ng kanin at ulam gamit ang mga daliri na walang natatapong pagkain',
            ],
            28 => [
                'domain_id' => 3,
                'competency' => 'Nakakakain nang mag-isa gamit ang kutsara ngunit walang natatapong pagkain',
            ],
            29 => [
                'domain_id' => 3,
                'competency' => 'Nakakakain  nang hindi na kailangang subuan pa.',
            ],
            30 => [
                'domain_id' => 3,
                'competency' => 'Tumutulong sa paghawak ng baso sa pag – inom',
            ],
            31 => [
                'domain_id' => 3,
                'competency' => 'Nakaiinom sa baso ngunit may natatapon',
            ],
            32 => [
                'domain_id' => 3,
                'competency' => 'Nakaiinom sa baso nang walang tumutulong.',
            ],
            33 => [
                'domain_id' => 3,
                'competency' => 'Nakakakuha nang inumin mag isa.',
            ],
            34 => [
                'domain_id' => 3,
                'competency' => 'Nakapagsasalin ng tubig (o anumang likido) mula sa pitsel na walang natatapon.',
            ],
            35 => [
                'domain_id' => 3,
                'competency' => 'Nakapaghahanda ng sariling pagkain / meryenda.',
            ],
            36 => [
                'domain_id' => 3,
                'competency' => 'Nakapaghahanda ng pagkain ng nakababatang kapatid/kapamilya kung walang kasamang matanda',
            ],
            37 => [
                'domain_id' => 3,
                'competency' => 'Nakikipagtulungan kung binibihisan (hal. Itinataas ang mga kamay at paa).',
            ],
            38 => [
                'domain_id' => 3,
                'competency' => 'Nakapaghuhubad ng shorts na may garter.',
            ],
            39 => [
                'domain_id' => 3,
                'competency' => 'Nakapaghuhubad ang sando.',
            ],
            40 => [
                'domain_id' => 3,
                'competency' => 'Nakapagbibihis mag-isa maliban sa pagbubutones at pagtatali ng laso ng sapatos',
            ],
            41 => [
                'domain_id' => 3,
                'competency' => 'Nakapagbibihis mag-isa at nakapagbubutones at nakapagtatali ng laso ng sapatos',
            ],
            42 => [
                'domain_id' => 3,
                'competency' => 'Naipakikita o naipahihiwatig na naihi o nadumi sa shorts',
            ],
            43 => [
                'domain_id' => 3,
                'competency' => 'Naipaalam sa tagapag-alaga ang pangangailangang umihi o dumumi upang makapunta sa tamang lugar (hal. banyo, CR)',
            ],
            44 => [
                'domain_id' => 3,
                'competency' => 'Nakapupunta sa tamang lugar upang umihi o dumumi ngunit paminsan-minsan ay may pagkakataong hindi mapigilang maihi o madumi sa shorts',
            ],
            45 => [
                'domain_id' => 3,
                'competency' => 'Nakapupunta sa tamang lugar upang umihi o dumumi',
            ],
            46 => [
                'domain_id' => 3,
                'competency' => 'Nalilinis ang sarili pagkatapos dumumi',
            ],
            47 => [
                'domain_id' => 3,
                'competency' => 'Nakikipagtulungan kung pinapaliguan (hal. Kinukuskos ang mga braso)',
            ],
            48 => [
                'domain_id' => 3,
                'competency' => 'Nakapaghuhugas at nakapagpupunas ng mga kamay nang walang tumutulong',
            ],
            49 => [
                'domain_id' => 3,
                'competency' => 'Nakapaghihilamos ng mukha nang walang tumutulong',
            ],
            50 => [
                'domain_id' => 3,
                'competency' => ' Nakaliligo mag-isa',
            ],
            51 => [
                'domain_id' => 4,
                'competency' => 'Naituturo ang kasapi ng pamilya na tinutukoy',
            ],
            52 => [
                'domain_id' => 4,
                'competency' => 'Naituturo ang 5 bahagi ng katawan na tinutukoy',
            ],
            53 => [
                'domain_id' => 4,
                'competency' => 'Naituturo ang 5 binanggit na larawan mula ipinakikitang aklat',
            ],
            54 => [
                'domain_id' => 4,
                'competency' => 'Nakasusunod sa isang antas na utos na may simpleng pang-ukol (hal. Sa ibabaw, sa ilalim, sa loob)',
            ],
            55 => [
                'domain_id' => 4,
                'competency' => 'Nakasusunod sa  dalawang antas na utos na may simpleng pang-ukol(hal. Kunin sa ilalim mesa ang bola at ilagay sa loob ng bag)',
            ],
            56 => [
                'domain_id' => 5,
                'competency' => 'Nakagagamit ng 5-20 nakikilalang salita (maliban sa mama at papa o kahlintulad nito)',
            ],
            57 => [
                'domain_id' => 5,
                'competency' => 'Nakagagamit ng panghalip (hal. Ako, akin)',
            ],
            58 => [
                'domain_id' => 5,
                'competency' => 'Nakagagamit ng 2-3 kombinasyon ng pandiwa-pantangi (verb-noun combinations) (hal.hingi gatas)',
            ],
            59 => [
                'domain_id' => 5,
                'competency' => 'Napangangalanan ang mga bagay na nakikita sa larawan',
            ],
            60 => [
                'domain_id' => 5,
                'competency' => 'Nakapagsasalita ng 2-3 tamang pangungusap ',
            ],
            61 => [
                'domain_id' => 5,
                'competency' => 'Nakapagtatanong ng “ano…”',
            ],
            62 => [
                'domain_id' => 5,
                'competency' => 'Nakapagtatanong ng “sino” at “bakit”',
            ],
            63 => [
                'domain_id' => 5,
                'competency' => 'Nakapagkukwento ng katatapos na karanasan (kapag tinanong/diniktahan) na naaayon sa pagkakasunod-sunod ng pangyayari gamit ang mga salitang tumutukoy sa pangnakaraan',
            ],
            64 => [
                'domain_id' => 6,
                'competency' => 'Nasusundan ng tingin ang direksyon ng nahuhulog na bagay',
            ],
            65 => [
                'domain_id' => 6,
                'competency' => 'Nahahanap ang mga bagay na nakatago o natatakpan',
            ],
            66 => [
                'domain_id' => 6,
                'competency' => 'Nagagaya ang mga kilos na kakakita pa lamang',
            ],
            67 => [
                'domain_id' => 6,
                'competency' => 'Nag-aalok ng isang bagay ngunit hindi ito binibitawan.',
            ],
            68 => [
                'domain_id' => 6,
                'competency' => 'Nahahanap ang nakatagong bagay.',
            ],
            69 => [
                'domain_id' => 6,
                'competency' => 'Nagpapakita ng simpleng pagpapanggap sa paglalaro',
            ],
            70 => [
                'domain_id' => 6,
                'competency' => 'Nakapagtutugma ng mga bagay-bagay',
            ],
            71 => [
                'domain_id' => 6,
                'competency' => 'Nakapagtutugma ng 2-3 kulay',
            ],
            72 => [
                'domain_id' => 6,
                'competency' => 'Nakapagtutugma ng mga larawan',
            ],
            73 => [
                'domain_id' => 6,
                'competency' => 'Nauuri ang mga hugis ayon sa sukat o kulay',
            ],
            74 => [
                'domain_id' => 6,
                'competency' => 'Nauuri ang mga bagay ayon sa 2 katangian (hal. laki at kulay)',
            ],
            75 => [
                'domain_id' => 6,
                'competency' => 'Naiaayos ang mga bagay mula sa pinakamaliit hanggang sa pinakamalaki',
            ],
            76 => [
                'domain_id' => 6,
                'competency' => 'Nakikilala ang 4 hanggang 6 na kulay',
            ],
            77 => [
                'domain_id' => 6,
                'competency' => 'Natutulad ang  mga hugis',
            ],
            78 => [
                'domain_id' => 6,
                'competency' => 'Nakababanggit ng 3 hayop o gulay kapag tinanong',
            ],
            79 => [
                'domain_id' => 6,
                'competency' => 'Nasasabi ang gamit ng mga bagay sa bahay',
            ],
            80 => [
                'domain_id' => 6,
                'competency' => 'Nakabubuo ng isang simpleng puzzle',
            ],
            81 => [
                'domain_id' => 6,
                'competency' => 'Nauunawaan ang magkasalungat na mga salita sa pamamagitan ng pagkumpleto ng pangungusap ( hal.Ang aso ay malaki , ang daga ay ______?',
            ],
            82 => [
                'domain_id' => 6,
                'competency' => 'Naituturo ang kaliwa at kanang bahagi ng katawan',
            ],
            83 => [
                'domain_id' => 6,
                'competency' => 'Nasasabi kung ano ang mali sa larawan ( hal. Ano ang mali sa larawang ito?)',
            ],
            84 => [
                'domain_id' => 6,
                'competency' => 'Napagtutugma ang malaki at maliit na mga titik',
            ],
            85 => [
                'domain_id' => 7,
                'competency' => 'Masayang pinapanood ang mga gawain ng mga tao o hayop sa malapit na lugar/kapaligiran',
            ],
            86 => [
                'domain_id' => 7,
                'competency' => 'Nakalalapit sa mga hindi kakilala ngunit sa simula ay maaaring maging mahiyain o hindi mapalagay.',
            ],
            87 => [
                'domain_id' => 7,
                'competency' => 'Nakapaglalarong mag-isa ngunit nais na malapit sa mga kakilalang nakatatanda o kapatid',
            ],
            88 => [
                'domain_id' => 7,
                'competency' => 'Tumatawa/tumitili nang malakas habang naglalaro',
            ],
            89 => [
                'domain_id' => 7,
                'competency' => 'Naglalaro ng "bulaga"',
            ],
            90 => [
                'domain_id' => 7,
                'competency' => 'Napagugulong ang bola papunta sa kalaro',
            ],
            91 => [
                'domain_id' => 7,
                'competency' => 'Niyayakap ang mga laruan',
            ],
            92 => [
                'domain_id' => 7,
                'competency' => 'Nagpapakita ng respeto sa nakatatanda gamit ang "opo" o "po" ( o anumang katumbas nito) sa halip na kanilang pangalan.',
            ],
            93 => [
                'domain_id' => 7,
                'competency' => 'Nagpapahihiram ng sariling laruan sa iba.',
            ],
            94 => [
                'domain_id' => 7,
                'competency' => 'Nagagaya ang mga ginagawa ng nakatatanda (hal. pagluluto, paghuhugas)',
            ],
            95 => [
                'domain_id' => 7,
                'competency' => 'Natutukoy ang damdamin ng iba.',
            ],
            96 => [
                'domain_id' => 7,
                'competency' => 'Naisasagawa ang mga kilos na naaayon sa kultura na hindi na hinihiling/iniuutos (hal. Pagmamano, paghalik).',
            ],
            97 => [
                'domain_id' => 7,
                'competency' => 'Naaliw ang mga kalaro o kapatid kung nababalisa/nag-aalala.',
            ],
            98 => [
                'domain_id' => 7,
                'competency' => 'Nagsisikap na masolusyunan kung may hadlang/problema sa kanyang nais gawin.',
            ],
            99 => [
                'domain_id' => 7,
                'competency' => 'Nakatutulong sa mga gawaing pambahay (hal. nagpupunas ng mesa, nagdidilig ng mga halaman).',
            ],
            100 => [
                'domain_id' => 7,
                'competency' => 'Nakapag-uusisa tungkol sa kapaligiran ngunit alam kung kailangang huminto sa pagtatanong.',
            ],
            101 => [
                'domain_id' => 7,
                'competency' => 'Nakapaghihintay ng pagkakataon (hal. Sa paghuhugas ng kamay, sa pagkuha ng pagkain).',
            ],
            102 => [
                'domain_id' => 7,
                'competency' => 'Nakahihingi ng permiso na malaro ang mga laruan na ginagamit ng ibang bata.',
            ],
            103 => [
                'domain_id' => 7,
                'competency' => 'Naipagtatanggol ang sariling pag-aari nang may determinasyon.',
            ],
            104 => [
                'domain_id' => 7,
                'competency' => 'Naglalaro nang maayos sa mga pangkatang laro (hal. hindi nandadaya para manalo).',
            ],
            105 => [
                'domain_id' => 7,
                'competency' => 'Naikukwento ang mga mabigat na nararamdaman (hal. Galit, lungkot).',
            ],
            106 => [
                'domain_id' => 7,
                'competency' => 'Natatanggap ang isang kasunduang ginawa ng tagapag-alaga (hal. Linisin muna ang kuwarto bago maglaro sa labas).',
            ],
            107 => [
                'domain_id' => 7,
                'competency' => 'Naipakikita ang responsibilidad sa pagbabantay sa mga nakababatang kapatid/myembro ng pamilya.',
            ],
            108 => [
                'domain_id' => 7,
                'competency' => 'Nakatutulong sa mga nakakatanda at nakababata sa anumang sitwasyon upang maiwasan ang bangayan/pakikipag-away',
            ],
        ];

        DB::table('tbl_ecdc_competencies')->insert($competencies);

    }
}
