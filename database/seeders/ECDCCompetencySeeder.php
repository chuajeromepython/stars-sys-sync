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
                'competency' => 'Nakaaakyat na ng mga silya o anumang mataas na muwebles tulad ng kama.',
            ],
            1 => [
                'domain_id' => 1,
                'competency' => 'Nakalalakad na nang paurong.',
            ],
            2 => [
                'domain_id' => 1,
                'competency' => 'Nakatatakbo na ng hindi nadadapa.',
            ],
            3 => [
                'domain_id' => 1,
                'competency' => 'Nakabababa na ng hagdan habang hawak ng tagapag-alaga ang isang kamay.',
            ],
            4 => [
                'domain_id' => 1,
                'competency' => 'Umaakyat na ng hagdan, parehong paa bawat baitang, habang nakahawak sa gabay ng hagdan.',
            ],
            5 => [
                'domain_id' => 1,
                'competency' => 'Nakaaakyat na ng hagdan na salitan ang mga paa na hindi na humahawak sa gabay ng hagdan.',
            ],
            6 => [
                'domain_id' => 1,
                'competency' => 'Nakabababa na ng hagdan na salitan ang mga paa na hindi na humahawak sa gabay ng hagdan.',
            ],
            7 => [
                'domain_id' => 1,
                'competency' => 'Naigagalaw ang mga parte ng katawan ayon sa ipinag-uutos',
            ],
            8 => [
                'domain_id' => 1,
                'competency' => 'Nakatatalon na.',
            ],
            9 => [
                'domain_id' => 1,
                'competency' => 'Nakahahagis na ng bola nang paitaas na may direksyon.',
            ],
            10 => [
                'domain_id' => 1,
                'competency' => 'Nakalulundag na ng 1-3 beses gamit ang mas gamay na paa.',
            ],
            11 => [
                'domain_id' => 1,
                'competency' => 'Nakatalon na nang may paikot.',
            ],
            12 => [
                'domain_id' => 1,
                'competency' => 'Nakasasayaw / Nakasusunod sa mga hakbang sa sayaw, grupong gawain ukol sa kilos at galaw.',
            ],
            13 => [
                'domain_id' => 2,
                'competency' => 'Kinakabig ang mga laruan o pagkain.',
            ],
            14 => [
                'domain_id' => 2,
                'competency' => 'Kinukuha ang mga bagay gamit ang hinlalaki at hintuturo.',
            ],
            15 => [
                'domain_id' => 2,
                'competency' => 'Nagpapakita ng higit na pagkagusto sa paggamit ng partikular na kamay',
            ],
            16 => [
                'domain_id' => 2,
                'competency' => 'Nailalagay/tinatanggal ang maliliit na bagay mula sa lalagyan',
            ],
            17 => [
                'domain_id' => 2,
                'competency' => 'Nahawakan ang krayola gamit ang nakasarang palad',
            ],
            18 => [
                'domain_id' => 2,
                'competency' => 'Natanggal ang takip ng bote/lalagyan, inaalis ang balot ng mga pagkain',
            ],
            19 => [
                'domain_id' => 2,
                'competency' => 'Kusang gumuguhit-guhit',
            ],
            20 => [
                'domain_id' => 2,
                'competency' => 'Gumuguhit ng patayo at pahalang na marka',
            ],
            21 => [
                'domain_id' => 2,
                'competency' => 'Kusang gumuguhit ng bilog na hugis',
            ],
            22 => [
                'domain_id' => 2,
                'competency' => 'Gumuguhit ng larawan ng tao (ulo, mata, katawan, braso, kamay, daliri, hita, paa)',
            ],
            23 => [
                'domain_id' => 2,
                'competency' => 'Gumuguhit ng bahay gamit ang iba\'t-ibang uri ng hugis (parisukat, tatsulok)',
            ],
            24 => [
                'domain_id' => 3,
                'competency' => 'Nakapagsusubo na ng mag-isa ng mga pagkain tulad ng biskwit at tinapay (finger food)',
            ],
            25 => [
                'domain_id' => 3,
                'competency' => 'Nakapagsusubo na ng mag-isa gamit ang kutsara ngunit may natatapong pagkain',
            ],
            26 => [
                'domain_id' => 3,
                'competency' => 'Nakapagsusubo na ng mag-isa gamit ang kutsara ngunit walang natatapong pagkain',
            ],
            27 => [
                'domain_id' => 3,
                'competency' => 'Nakapagsusubo na ng mag-isa ng ulam at kanin gamit ang mga daliri na walang natatapong pagkain',
            ],
            28 => [
                'domain_id' => 3,
                'competency' => 'Nakapagsusubo na ng mag-isa ng ulam at kanin gamit ang mga daliri ngunit may natatapong pagkain',
            ],
            29 => [
                'domain_id' => 3,
                'competency' => 'Nakakakain ng mag-isa na hindi na kailangang subuan pa',
            ],
            30 => [
                'domain_id' => 3,
                'competency' => 'Tumutulong sa paghawak ng baso/tasa sa pag-inom',
            ],
            31 => [
                'domain_id' => 3,
                'competency' => 'Umiinom sa baso ngunit may natatapon',
            ],
            32 => [
                'domain_id' => 3,
                'competency' => 'Umiinom sa baso na walang umaalalay',
            ],
            33 => [
                'domain_id' => 3,
                'competency' => 'Kumukuha ng inumin nang mag-isa',
            ],
            34 => [
                'domain_id' => 3,
                'competency' => 'Binubuhos ang tubig (o anumang likido) mula sa pitsel na walang natatapon',
            ],
            35 => [
                'domain_id' => 3,
                'competency' => 'Naghahanda ng sariling pagkain/meryenda',
            ],
            36 => [
                'domain_id' => 3,
                'competency' => 'Naghahanda ng pagkain para sa nakakabatang kapatid/ibang miyembro ng pamilya kung walang matanda sa bahay',
            ],
            37 => [
                'domain_id' => 3,
                'competency' => 'Nakikipagtulungan kung binibihisan (hal. Itinataas ang mga kamay at paa).',
            ],
            38 => [
                'domain_id' => 3,
                'competency' => 'Nahuhubad ang shorts na may garter na mag-isa',
            ],
            39 => [
                'domain_id' => 3,
                'competency' => 'Nahuhubad ang sando na mag-isa',
            ],
            40 => [
                'domain_id' => 3,
                'competency' => 'Nabibihisan ang sarili na walang tumutulong, maliban sa pagbubutones at pagtatali',
            ],
            41 => [
                'domain_id' => 3,
                'competency' => 'Nabibihisan ang sarili na walang tumutulong, kasama na ang pagbubutones at pagtatali',
            ],
            42 => [
                'domain_id' => 3,
                'competency' => 'Ipinakita o ipinahiwatig na naihi o nadumi sa shorts',
            ],
            43 => [
                'domain_id' => 3,
                'competency' => 'Pinapaalam sa tagapag-alaga ang pangangailangang umihi o dumumi upang makapunta sa tamang lugar (hal., banyo, CR)',
            ],
            44 => [
                'domain_id' => 3,
                'competency' => 'Pumupunta sa tamang lugar upang umihi o dumumi ngunit paminsan-minsan ay may pagkakataong hindi mapigilang maihi o madumi sa shorts',
            ],
            45 => [
                'domain_id' => 3,
                'competency' => 'Matagumpay na pumupunta sa tamang lugar upang umihi o dumumi',
            ],
            46 => [
                'domain_id' => 3,
                'competency' => 'Pinupunasan ang sarili pagkatapos dumumi',
            ],
            47 => [
                'domain_id' => 3,
                'competency' => 'Nakikipagtulungan kung pinapaliguan (hal., kinukuskos ang mga braso)',
            ],
            48 => [
                'domain_id' => 3,
                'competency' => 'Naghuhugas at nagpupunas ng mga kamay na walang tumutulong',
            ],
            49 => [
                'domain_id' => 3,
                'competency' => 'Naghihilamos ng mukha nang walang tumutulong',
            ],
            50 => [
                'domain_id' => 3,
                'competency' => 'Naliligo nang walang tumutulong',
            ],
            51 => [
                'domain_id' => 4,
                'competency' => 'Tinuturo ang mga kapamilya o pamilyar na bagay kapag ipinaturo',
            ],
            52 => [
                'domain_id' => 4,
                'competency' => 'Tinuturo ang 5 parte ng katawan kung inuutusan',
            ],
            53 => [
                'domain_id' => 4,
                'competency' => 'Tinuturo ang 5 napangalanang larawan ng mga bagay',
            ],
            54 => [
                'domain_id' => 4,
                'competency' => 'Sumusunod sa isang lebel na utos na may simpleng pang-ukol (hal., sa ibabaw, sa ilalim)',
            ],
            55 => [
                'domain_id' => 4,
                'competency' => 'Sumusunod sa dalawang lebel na utos na may simpleng pang-ukol',
            ],
            56 => [
                'domain_id' => 5,
                'competency' => 'Gumagamit ng 5-20 nakikilalang salita',
            ],
            57 => [
                'domain_id' => 5,
                'competency' => 'Gumagamit ng panghalip (hal., ako akin)',
            ],
            58 => [
                'domain_id' => 5,
                'competency' => 'Gumagamit ng 2-3 kombinasyon ng pandiwa-pantangi (verb-noun combinations) [hal., hingi pera]',
            ],
            59 => [
                'domain_id' => 5,
                'competency' => 'Napapangalanan ang mga bagay na nakikita sa larawan (4)',
            ],
            60 => [
                'domain_id' => 5,
                'competency' => 'Nagsasalita sa tamang pangungusap na may 2-3 salita',
            ],
            61 => [
                'domain_id' => 5,
                'competency' => 'Nagtatanong ng ano',
            ],
            62 => [
                'domain_id' => 5,
                'competency' => 'Nagtatanong ng sino at bakit',
            ],
            63 => [
                'domain_id' => 5,
                'competency' => 'Kinukuwento ang mga katatapos na karanasan (kapag tinanong/diniktahan) na naayon sa pagkakasunod-sunod ng pangyayari gamit ang mga salitang tumutukoy sa pangnakaraan (past tense)',
            ],
            64 => [
                'domain_id' => 6,
                'competency' => 'Tinitingnan ang direksyon ng nahuhulog na bagay',
            ],
            65 => [
                'domain_id' => 6,
                'competency' => 'Hinahanap ang mga bagay na bahagyang nakatago',
            ],
            66 => [
                'domain_id' => 6,
                'competency' => 'Ginagaya ang mga kilos na kakakita pa lamang',
            ],
            67 => [
                'domain_id' => 6,
                'competency' => 'Binibigay ang bagay ngunit hindi ito binibitiwan',
            ],
            68 => [
                'domain_id' => 6,
                'competency' => 'Hinahanap ang mga bagay na lubusang nakatago',
            ],
            69 => [
                'domain_id' => 6,
                'competency' => 'Naglalaro ng kunwakunwarian',
            ],
            70 => [
                'domain_id' => 6,
                'competency' => 'Tinutugma ang mga bagay',
            ],
            71 => [
                'domain_id' => 6,
                'competency' => 'Tinutugma ang 2-3 kulay',
            ],
            72 => [
                'domain_id' => 6,
                'competency' => 'Tinutugma ang mga larawan',
            ],
            73 => [
                'domain_id' => 6,
                'competency' => 'Nauuri ang mga hugis ayon sa sukat o kulay',
            ],
            74 => [
                'domain_id' => 6,
                'competency' => 'Inaayos ang mga bagay ayon sa 2 katangian (hal. laki at kulay)',
            ],
            75 => [
                'domain_id' => 6,
                'competency' => 'Inaayos ang mga bagay mula sa pinakamaliit hanggang sa pinakamalaki',
            ],
            76 => [
                'domain_id' => 6,
                'competency' => 'Pinapangalan ang 4-6 na kulay',
            ],
            77 => [
                'domain_id' => 6,
                'competency' => 'Gumuguhit/ginagaya ang isang disenyo',
            ],
            78 => [
                'domain_id' => 6,
                'competency' => 'Pinapangalanan ang 3 hayop o gulay kapag tinanong',
            ],
            79 => [
                'domain_id' => 6,
                'competency' => 'Sinasabi ang mga gamit ng mga bagay sa bahay',
            ],
            80 => [
                'domain_id' => 6,
                'competency' => 'Nakakabuo ng isang simpleng puzzle',
            ],
            81 => [
                'domain_id' => 6,
                'competency' => 'Naiintindihan ang magkakasalungat na mga salita sa pamamagitan ng pagkumpleto ng pangungusap (hal., Ang aso ay malaki, ang daga ay ____)',
            ],
            82 => [
                'domain_id' => 6,
                'competency' => 'Tinuturo ang kaliwa at kanang bahagi ng katawan',
            ],
            83 => [
                'domain_id' => 6,
                'competency' => 'Nasasabi kung ano ang mali sa larawan (hal., Ano ang mali sa larawan?)',
            ],
            84 => [
                'domain_id' => 6,
                'competency' => 'Tunutugma ang malalaki at maliliit na mga titik',
            ],
            85 => [
                'domain_id' => 7,
                'competency' => 'Natutuwang nanonood ng mga ginagawa ng mga tao o hayop sa malapit na lugar',
            ],
            86 => [
                'domain_id' => 7,
                'competency' => 'Lumalapit sa mga hindi kakilala ngunit sa una ay maaaring maging mahiyain o hindi mapalagay',
            ],
            87 => [
                'domain_id' => 7,
                'competency' => 'Naglalarong mag-isa ngunit gustong malapit sa mga pamilyar na nakatatanda o kapatid',
            ],
            88 => [
                'domain_id' => 7,
                'competency' => 'Tumatawa/tumitili nang malakas sa paglalaro',
            ],
            89 => [
                'domain_id' => 7,
                'competency' => 'Naglalaro ng "bulaga"',
            ],
            90 => [
                'domain_id' => 7,
                'competency' => 'Napagugulong ang bola sa kalaro o tagapag-alaga',
            ],
            91 => [
                'domain_id' => 7,
                'competency' => 'Niyayakap ang mga laruan',
            ],
            92 => [
                'domain_id' => 7,
                'competency' => 'Nagpapakita ng respeto sa nakataanda gamit ang “Opo”, “Po” (o anumang katumbas nito) sa halip na kanilang unang pangalan',
            ],
            93 => [
                'domain_id' => 7,
                'competency' => 'Pinahihiram ang sariling laruan sa iba',
            ],
            94 => [
                'domain_id' => 7,
                'competency' => 'Ginagaya ang mga ginagawa ng mga nakatatanda (hal., pagluluto, paghuhugas)',
            ],
            95 => [
                'domain_id' => 7,
                'competency' => 'Natutukoy ang nararamdaman ng iba',
            ],
            96 => [
                'domain_id' => 7,
                'competency' => 'Naisasagawa nang tama ang mga nakasanayang pag-uugali nang hindi pinaaalahanan (hal., pagmamano, paghalik)',
            ],
            97 => [
                'domain_id' => 7,
                'competency' => 'Napasasaya ang nalulungkot na mga kalaro.',
            ],
            98 => [
                'domain_id' => 7,
                'competency' => 'Nagsusumikap kung may mga problema at hadlang sa kaniyang mga nais/gusto.',
            ],
            99 => [
                'domain_id' => 7,
                'competency' => 'Tumutulong sa mga gawaing pambahay (hal., Nagpupunas ng mesa, magdidilig ng mga haalman)',
            ],
            100 => [
                'domain_id' => 7,
                'competency' => 'Naipakikita ang pagiging mausisa sa kapaligiran subalit nalalaman kung kailan dapat huminto sa pagtatanong sa nakatatanda.',
            ],
            101 => [
                'domain_id' => 7,
                'competency' => 'Nakapaghihintay sa kaniyang panahon/oras',
            ],
            102 => [
                'domain_id' => 7,
                'competency' => 'Nakahihingi ng pahintulot na mahiram ang laruan na nilalaro ng iba',
            ],
            103 => [
                'domain_id' => 7,
                'competency' => 'Naipaglalaban nang may determinasyon ang sariling gamit',
            ],
            104 => [
                'domain_id' => 7,
                'competency' => 'Nakikipaglaro ng maayos sa grupo (hal. Hindi nandadaya para manalo)',
            ],
            105 => [
                'domain_id' => 7,
                'competency' => 'Nasasabi ang mga nararanasang hindi magandang nararamdaman (hal galit, lingkot, pag-aalala)',
            ],
            106 => [
                'domain_id' => 7,
                'competency' => 'Tinatanggap ang isang kasunduang ginawa ng tagapag-alaga (la., luinisin muna ang kuwarto bago maglaro sa labas)',
            ],
            107 => [
                'domain_id' => 7,
                'competency' => 'Responsableng nagbabantay sa mga nakababatang kapatid/ ibang miyembro ng pamilya',
            ],
            108 => [
                'domain_id' => 7,
                'competency' => 'Nakikipagtulungan sa mga nakakatanda at nakababata sa anumang sitwasyon upang maiwasan ang pag-aaway at alitan',
            ],
        ];


        DB::table('tbl_ecdc_competencies')->insert($competencies);
    }
}
