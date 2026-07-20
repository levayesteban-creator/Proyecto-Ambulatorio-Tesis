<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SisDiagnosisSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sis_diagnoses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();

        // 270 diagnósticos — Fuente: MORBILIDAD DIAGNOSTICOS PARA EPI 12, EPI 15 Y PROGRAMAS
        // Cuando el documento lista varios códigos CIE, se usa el primero como code principal.
        $diagnoses = [
            // 01-14: Infecciosas intestinales
            ['code' => 'A00',     'name' => 'CÓLERA'],
            ['code' => 'A06',     'name' => 'AMIBIASIS'],
            ['code' => 'A08, A09', 'name' => 'DIARREAS < 1 AÑO'],
            ['code' => 'A08, A09', 'name' => 'DIARREAS 1-4 AÑOS'],
            ['code' => 'A08, A09', 'name' => 'DIARREAS 5 AÑOS Y MAS'],
            ['code' => 'A08, A09', 'name' => 'DIARREAS SIN DESHIDRATACION'],
            ['code' => 'A08, A09', 'name' => 'DIARREA CON DESHIDRATACION LEVE / MODERADA'],
            ['code' => 'A08, A09', 'name' => 'DIARREA CON DESHIDRATACION GRAVE'],
            ['code' => 'A07.1',   'name' => 'GIARDIASIS'],
            ['code' => 'B65, B68, B70, B83', 'name' => 'HELMINTIASIS'],
            ['code' => 'A01.0',   'name' => 'FIEBRE TIFOIDEA'],
            ['code' => null,      'name' => 'ETA N° DE BROTES'],
            ['code' => null,      'name' => 'CASOS ASOC. A BROTES DE ENFERM. TRANSM. POR ALIMENTOS'],
            ['code' => 'B15',     'name' => 'HEPATITIS AGUDA TIPO A'],

            // 15-19: Tuberculosis e influenza
            ['code' => 'A15, A19', 'name' => 'TUBERCULOSIS'],
            ['code' => 'A15.0, A15.3', 'name' => 'TUBERCULOSIS SERIE P (PULMONAR BK +)'],
            ['code' => 'A16.0',   'name' => 'TUBERCULOSIS SERIE N (PULMONAR BK -)'],
            ['code' => 'A16.3, A16.9, A17, A19', 'name' => 'TUBERCULOSIS SERIE EP (EXTRAPULMONARES)'],
            ['code' => 'J09, J11', 'name' => 'INFLUENZA, ENF. TIPO INFLUENZA'],

            // 20-33: ITS / VIH
            ['code' => 'A54',     'name' => 'INFECCION GONOCOCICA'],
            ['code' => 'A51, A53', 'name' => 'SIFILIS'],
            ['code' => 'A50',     'name' => 'SIFILIS CONGENITA'],
            ['code' => 'Z21',     'name' => 'INFECCION ASINTOMATICA VIH'],
            ['code' => 'B20, B24', 'name' => 'ENFERMEDAD VIH/SIDA'],
            ['code' => 'A63.0',   'name' => 'CONDILoma ACUMINADO'],
            ['code' => 'A59.9',   'name' => 'TRICOMONIASIS'],
            ['code' => 'B37.3',   'name' => 'CANDIDIASIS GENITAL'],
            ['code' => 'N76.6, N50.8', 'name' => 'ULCERA GENITAL'],
            ['code' => 'N89.8',   'name' => 'FLUJO VAGINAL'],
            ['code' => 'N50.8',   'name' => 'EDEMA DE ESCROTO'],
            ['code' => 'N51, A54.0, A59.0', 'name' => 'FLUJO URETRAL'],
            ['code' => 'I88.8',   'name' => 'BUBON INGUINAL'],
            ['code' => 'B97.7',   'name' => 'INFECCION POR VIRUS PAPILOMA HUMANO'],

            // 34-45: Otras infecciosas
            ['code' => 'A80',     'name' => 'POLIOMIELITIS'],
            ['code' => 'A37',     'name' => 'TOSFERINA'],
            ['code' => 'B26',     'name' => 'PAROTIDITIS INFECTIOSA'],
            ['code' => 'A33',     'name' => 'TETANOS NEONATAL'],
            ['code' => 'A34',     'name' => 'TETANOS OBSTETRICO'],
            ['code' => 'A35',     'name' => 'OTROS TETANOS'],
            ['code' => 'A36',     'name' => 'DIFTERIA'],
            ['code' => 'B05',     'name' => 'SARAMPION'],
            ['code' => 'B06',     'name' => 'RUBEOLA'],
            ['code' => 'A90',     'name' => 'DENGUE SIN SIGNOS DE ALARMA'],
            ['code' => 'A90',     'name' => 'DENGUE CON SIGNOS DE ALARMA'],
            ['code' => 'A91',     'name' => 'DENGUE GRAVE'],

            // 46-63: Arbovirosis y parasitosis
            ['code' => 'A92.0',   'name' => 'CHIKUNGUNYA'],
            ['code' => 'U06',     'name' => 'ZIKA'],
            ['code' => 'A92.2',   'name' => 'ENCEFALITIS EQUINA VENEZOLANA'],
            ['code' => 'A95',     'name' => 'FIEBRE AMARILLA'],
            ['code' => 'B50',     'name' => 'PALUDISMO A FALCIPARUM'],
            ['code' => 'B51',     'name' => 'PALUDISMO A VIVAX'],
            ['code' => 'B52',     'name' => 'PALUDISMO A MALARIAE'],
            ['code' => 'B50, B51, B52', 'name' => 'PALUDISMO MIXTA'],
            ['code' => 'B55.0',   'name' => 'LEISHMANIASIS VISCERAL'],
            ['code' => 'B55.1',   'name' => 'LEISHMANIASIS CUTÁNEA'],
            ['code' => 'B55.2',   'name' => 'LEISHMANIASIS MUCOCUTÁNEA'],
            ['code' => 'B55.9',   'name' => 'LEISHMANIASIS NO ESPECIFICA'],
            ['code' => 'B57.0, B57.1', 'name' => 'ENFERMEDAD DE CHAGAS AGUDA'],
            ['code' => 'B57.2, B57.5', 'name' => 'ENFERMEDAD DE CHAGAS CRÓNICA'],
            ['code' => 'B73',     'name' => 'ONCOCERCOSIS'],
            ['code' => 'A92.3',   'name' => 'FIEBRE DEL OESTE DEL NILO'],
            ['code' => 'A82',     'name' => 'RABIA HUMANA'],
            ['code' => 'A96.8',   'name' => 'FIEBRE HEMORRAGICA VENEZOLANA'],
            ['code' => 'A27',     'name' => 'LEPTOSPIROSIS'],

            // 64-80: Otras infecciosas
            ['code' => 'A23',     'name' => 'BRUCELOSIS'],
            ['code' => 'B69',     'name' => 'CISTICERCOSIS'],
            ['code' => 'A20',     'name' => 'PESTE'],
            ['code' => null,      'name' => 'RUMOR DE EPIZOOTIAS'],
            ['code' => 'A87',     'name' => 'MENINGITIS VIRAL'],
            ['code' => 'G00',     'name' => 'MENINGITIS BACTERIANA'],
            ['code' => 'A39.0',   'name' => 'MENINGITIS MENINGOCÓCICA'],
            ['code' => 'A39.9',   'name' => 'ENFERMEDAD MENINGOCÓCICA'],
            ['code' => 'B01',     'name' => 'VARICELA'],
            ['code' => 'B16',     'name' => 'HEPATITIS AGUDA TIPO B'],
            ['code' => 'B17.1',   'name' => 'HEPATITIS AGUDA TIPO C'],
            ['code' => 'B17',     'name' => 'HEPATITIS OTRAS AGUDAS'],
            ['code' => 'B19',     'name' => 'HEPATITIS NO ESPECIFICADA'],
            ['code' => 'B34',     'name' => 'SINDROME VIRAL'],
            ['code' => 'B85, B87', 'name' => 'PEDICULOSIS Y ÁCAROS'],
            ['code' => 'B86',     'name' => 'ESCABIOSIS'],
            ['code' => 'B35, B36', 'name' => 'MICOSIS SUPERFICIAL'],

            // 81-90: Infecciosas varias
            ['code' => 'A30',     'name' => 'LEPRA'],
            ['code' => 'A98.4',   'name' => 'EBOLA'],
            ['code' => 'B33.4',   'name' => 'HANTAVIRUS SCPH'],
            ['code' => 'B27',     'name' => 'MONONUCLEOSIS INFECCIOSA'],
            ['code' => 'B03',     'name' => 'VIRUELA'],
            ['code' => 'C00, D48', 'name' => 'NEOPLASIAS'],
            ['code' => 'C80.9',   'name' => 'TUMORES MALIGNOS NO ESPECIFICADO'],
            ['code' => 'D50, D64', 'name' => 'ANEMIAS'],
            ['code' => 'E00, E07', 'name' => 'TRASTORNOS TIROIDEOS'],

            // 91-97: Metabólicas / Nutricionales
            ['code' => 'E10, E14', 'name' => 'DIABETES MELLITUS TIPO 1'],
            ['code' => 'E10, E14', 'name' => 'DIABETES MELLITUS TIPO 2'],
            ['code' => 'E44.1',   'name' => 'DESNUTRICION LEVE < 15 AÑOS'],
            ['code' => 'E44.0',   'name' => 'DESNUTRICION MODERADA < 15 AÑOS'],
            ['code' => 'E40, E42, E45', 'name' => 'DESNUTRICION GRAVE < 15 AÑOS'],
            ['code' => 'E66.9',   'name' => 'SOBREPESO'],
            ['code' => 'E66',     'name' => 'OBESIDAD MÓRBIDA'],

            // 98-109: Salud mental
            ['code' => 'F00, F99', 'name' => 'TRAST. MENT. Y DEL COMPORTAMIENTO'],
            ['code' => 'F00, F09', 'name' => 'TRAST. MENT. ORGÁNICOS'],
            ['code' => 'F10, F19', 'name' => 'TRAST. MENT. POR USO SUSTANCIAS PSICOACTIVAS'],
            ['code' => 'F20, F29', 'name' => 'ESQUIZOFRENIA, ESQUIZOTÍPICOS Y TRAST. DELIRANTES'],
            ['code' => 'F30, F39', 'name' => 'TRASTORNOS DEL HUMOR'],
            ['code' => 'F40, F48', 'name' => 'TRASTORNOS NEUROTICOS DEL ESTRÉS'],
            ['code' => 'F50, F59', 'name' => 'SINDR. DEL COMPORTAMIENTO ASOC. A FACT. FISICOS'],
            ['code' => 'F60, F69', 'name' => 'TRASTORNOS DE LA PERSONALIDAD'],
            ['code' => 'F70, F79', 'name' => 'RETARDO MENTAL'],
            ['code' => 'F80',     'name' => 'TRASTORNOS DEL DESARROLLO PSICOLÓGICO'],
            ['code' => 'F90, F98', 'name' => 'TRASTORNOS EMOCIONALES Y DEL COMPORTAMIENTO'],
            ['code' => 'F99',     'name' => 'TRASTORNO MENTAL NO ESPECIFICADO'],

            // 110-114: Neurológicas
            ['code' => 'G82.0',   'name' => 'PARÁLISIS FLÁCIDA < 15 AÑOS'],
            ['code' => 'G82.0',   'name' => 'PARÁLISIS FLÁCIDA 15 AÑOS Y MÁS'],
            ['code' => 'G61.0',   'name' => 'SÍNDROME DE GUILLAIN BARRÉ'],
            ['code' => 'G40, G41', 'name' => 'EPILEPSIA'],
            ['code' => 'G43',     'name' => 'MIGRAÑA'],

            // 115-128: Oculares / Oído
            ['code' => 'H10',     'name' => 'CONJUNTIVITIS'],
            ['code' => 'H52',     'name' => 'TRASTORNOS DE LA ACOMODACIÓN Y REFRACCIÓN'],
            ['code' => 'B210',    'name' => 'PERSONA CIEGA (PC)'],
            ['code' => 'B210',    'name' => 'DEFICIENCIA VISUAL SEVERA (DVS)'],
            ['code' => 'B210',    'name' => 'DEFICIENCIA VISUAL (DV)'],
            ['code' => 'H00, H09, H11, H59', 'name' => 'OTRAS ENFERMEDADES DEL OJO'],
            ['code' => 'H36, E14.3', 'name' => 'RETINOPATIA DIABETICA'],
            ['code' => 'H40',     'name' => 'GLAUCOMA'],
            ['code' => 'H25, H26, H27, H28', 'name' => 'CATARATA'],
            ['code' => 'H53.0',   'name' => 'AMBLIOPIA'],
            ['code' => 'H60',     'name' => 'OTITIS EXTERNA'],
            ['code' => 'H65.0, H65.1, H66.0, H66.9', 'name' => 'OTITIS MEDIA AGUDA < 5 AÑOS'],
            ['code' => 'H65.0, H65.1, H66.0, H66.9', 'name' => 'OTITIS MEDIA AGUDA ≥ 5 AÑOS'],
            ['code' => 'H65.2, H65.9', 'name' => 'OTITIS MEDIA CRÓNICA'],

            // 129-135: Cardiovasculares (parcial)
            ['code' => 'I00',     'name' => 'FIEBRE REUMAT. Y ENF.CARD.REUM. CRON.'],
            ['code' => 'I00',     'name' => 'FIEBRE REUMÁTICA SIN COMPLICACIONES CARDÍACAS'],
            ['code' => 'I10',     'name' => 'HIPERTENSIÓN ARTERIAL < 15 AÑOS'],
            ['code' => 'I10',     'name' => 'HIPERTENSIÓN ARTERIAL 15-44 AÑOS'],
            ['code' => 'I10',     'name' => 'HIPERTENSIÓN ARTERIAL 45 AÑOS Y MÁS'],
            ['code' => 'I20',     'name' => 'ENFERM. ISQUÉMICAS DEL CORAZÓN'],
            ['code' => 'I21',     'name' => 'INFARTO AGUDO DEL MIOCARDIO < 45 AÑOS'],

            // 136-141: Cardiovasculares (continuación)
            ['code' => 'I21',     'name' => 'INFARTO AGUDO DEL MIOCARDIO 45 AÑOS Y MAS'],
            ['code' => 'I60, I69', 'name' => 'ENFERMEDADES CEREBROVASCULARES'],
            ['code' => 'I83',     'name' => 'VÁRICES DE MIEMBROS INFERIORES'],
            ['code' => 'I50',     'name' => 'INSUFICIENCIA CARDÍACA'],
            ['code' => 'I20',     'name' => 'ANGINA DE PECHO'],
            ['code' => 'I25.9',   'name' => 'CARDIOPATÍA ISQUÉMICA'],

            // 142-166: Respiratorias
            ['code' => 'J00',     'name' => 'RINOFARINGITIS AGUDA < 5 AÑOS'],
            ['code' => 'J00',     'name' => 'RINOFARINGITIS AGUDA ≥ 5 AÑOS'],
            ['code' => 'J02',     'name' => 'FARINGITIS AGUDA < 5 AÑOS'],
            ['code' => 'J02',     'name' => 'FARINGITIS AGUDA ≥ 5 AÑOS'],
            ['code' => 'J03',     'name' => 'AMIGDALITIS AGUDA'],
            ['code' => 'J12, J18', 'name' => 'NEUMONIA < 1 AÑO'],
            ['code' => 'J12, J18', 'name' => 'NEUMONIA 1-4 AÑOS'],
            ['code' => 'J12, J18', 'name' => 'NEUMONÍAS 5 AÑOS Y MAS'],
            ['code' => 'J12, J18', 'name' => 'NEUMONIA GRAVE < 2 MESES'],
            ['code' => 'J21',     'name' => 'BRONQUIOLITIS AGUDA < 2 AÑOS'],
            ['code' => 'J20',     'name' => 'BRONQUITIS AGUDA'],
            ['code' => 'J41, J42, J44.8', 'name' => 'BRONQUITIS CRÓNICA'],
            ['code' => 'J45',     'name' => 'ASMA < 10 AÑOS'],
            ['code' => 'J45',     'name' => 'ASMA ≥ 10 AÑOS'],
            ['code' => 'J30.1, J30.4', 'name' => 'RINITIS ALÉRGICA'],
            ['code' => 'J44.9',   'name' => 'ENFERMEDAD PULMONAR OBSTRUCTIVA CRÓNICA'],
            ['code' => 'J01',     'name' => 'SINUSITIS AGUDA'],
            ['code' => 'J04',     'name' => 'LARINGITIS Y TRAQUEÍTIS AGUDA'],
            ['code' => 'J05',     'name' => 'LARINGITIS OBSTRUCTIVA Y EPIGLOTITIS'],
            ['code' => 'J06',     'name' => 'IRA VIAS RESP. SUP. Y SITIOS MULTIPLES NO ESPECIF'],
            ['code' => 'J22',     'name' => 'IRA NO ESPECIF. VIAS RESP INFERIORES'],
            ['code' => 'J22',     'name' => 'INFECCION RESPIRATORIA AGUDA GRAVE'],
            ['code' => 'U04.9',   'name' => 'SÍNDROME RESPIRATORIO AGUDO SEVERO SARS'],
            ['code' => null,      'name' => 'SINTOMATICO RESPIRATORIO'],
            ['code' => null,      'name' => 'EXACERBACION AGUDA DE EPOC'],

            // 167-175: Digestivas / Estomatológicas
            ['code' => 'K02',     'name' => 'CARIES DENTAL'],
            ['code' => 'K05.0, K05.1', 'name' => 'GINGIVITIS'],
            ['code' => 'K12.0, K12.1', 'name' => 'ESTOMATITIS'],
            ['code' => 'K29',     'name' => 'GASTRITIS'],
            ['code' => 'K35, K37', 'name' => 'APENDICITIS TODAS FORMAS'],
            ['code' => 'K40, K46', 'name' => 'HERNIA CAVIDAD ABDOMINAL'],
            ['code' => 'K80',     'name' => 'COLELITIASIS'],
            ['code' => 'K85',     'name' => 'PANCREATITIS AGUDA'],
            ['code' => 'K22, K31, K63', 'name' => 'OTRAS ENF. ESOF., ESTÓMAGO E INTESTINO'],

            // 176-180: Piel / Tejido blando
            ['code' => 'L01',     'name' => 'ABSCESOS'],
            ['code' => 'L03',     'name' => 'CELULITIS'],
            ['code' => 'L20, L30', 'name' => 'DERMATITIS'],
            ['code' => 'L08.0',   'name' => 'PIODERMITIS'],
            ['code' => 'L50',     'name' => 'URTICARIA'],

            // 181-190: Musculoesqueléticas
            ['code' => 'M00, M14', 'name' => 'ARTRITIS'],
            ['code' => 'M79.1',   'name' => 'MIALGIAS'],
            ['code' => 'M79.2',   'name' => 'NEURALGIAS'],
            ['code' => 'M70, M71, M75, M77', 'name' => 'BURSITIS'],
            ['code' => 'N39.0',   'name' => 'INFECCION URINARIA'],

            // 187-197: Urológicas / Ginecológicas
            ['code' => 'N34.1',   'name' => 'URETRITIS NO GONOCÓCICA'],
            ['code' => 'N89.8',   'name' => 'LEUCORREA NO ESPECIFICADA'],
            ['code' => 'N93.9',   'name' => 'HEMORR. GENITAL NO ESPEC. HEMBRAS'],
            ['code' => 'N23',     'name' => 'CÓLICO NEFRÍTICO'],
            ['code' => 'N94.6',   'name' => 'DISMENORREA NO ESPECIFICADA'],
            ['code' => 'N71, N72', 'name' => 'SALPINGITIS Y OOFORITIS'],
            ['code' => 'N00, N08, N10, N13.3, N14, N20.0, N25, N29.8', 'name' => 'ENFERM. INFLAMATORIAS DEL UTERO'],
            ['code' => 'N76.1',   'name' => 'ENFERM. RENAL'],
            ['code' => 'N17.9',   'name' => 'INSUFICIENCIA RENAL AGUDA'],
            ['code' => 'N20.0',   'name' => 'CALCULO DEL RIÑON Y DEL URETER'],
            ['code' => 'N30.9',   'name' => 'CISTITIS'],

            // 197-211: Obstetricas
            ['code' => 'O20, O44', 'name' => 'HEMORRAGIA 1er TRIMESTRE'],
            ['code' => 'O20, O44', 'name' => 'HEMORRAGIA 2° TRIMESTRE'],
            ['code' => 'O20, O44, O46', 'name' => 'HEMORRAGIA 3er TRIM. EMBARAZO'],
            ['code' => 'O10',     'name' => 'HIPERTENSIÓN PREVIA'],
            ['code' => 'O13, O14', 'name' => 'PRE-ECLAMPSIA'],
            ['code' => 'O15',     'name' => 'ECLAMPSIA'],
            ['code' => 'O07',     'name' => 'INTENTO FALLIDO DE ABORTO'],
            ['code' => 'O00, O06, O08', 'name' => 'ABORTO'],
            ['code' => 'O42.9',   'name' => 'RUPTURA PREMATURA MEMBRANA'],
            ['code' => 'O91, O92', 'name' => 'TRAST. MAMARIOS DEL PUERPERIO'],
            ['code' => 'O24.1',   'name' => 'DM PREGESTACIONAL I'],
            ['code' => 'O24.2',   'name' => 'DM PREGESTACIONAL II'],
            ['code' => 'O24.4',   'name' => 'DIABETES GESTACIONAL'],
            ['code' => 'O85, O86', 'name' => 'INFECCIONES PUERPERALES'],
            ['code' => 'O72.1, O72.2', 'name' => 'HEMORRAGIAS PUERPERALES'],
            ['code' => 'N71',     'name' => 'ENDOMETRITIS'],
            ['code' => 'O90.1',   'name' => 'DEHISCENCIA DE EPISIORRAFIA'],
            ['code' => 'O75.4',   'name' => 'ABSCESO DE PARED ABDOMINAL'],

            // 215-225: Puerperio / Congénitas
            ['code' => 'O99.3',   'name' => 'DEPRESION POSTPARTO'],
            ['code' => 'O91',     'name' => 'MASTITIS'],
            ['code' => 'O92.1',   'name' => 'GRIETAS O SIGNOS DE INFECCIÓN EN LOS PEZONES'],
            ['code' => 'O99.8',   'name' => 'OTRAS COMPLICACIONES DEL EMB.PART. Y PUERPERIO'],
            ['code' => 'P35.0',   'name' => 'SÍNDROME DE RUBÉOLA CONGÉNITA'],
            ['code' => 'R51',     'name' => 'CEFALEA'],
            ['code' => 'R50',     'name' => 'FIEBRE'],
            ['code' => 'R56',     'name' => 'CONVULSIONES'],
            ['code' => 'R10.0',   'name' => 'ABDOMEN AGUDO'],
            ['code' => 'R10.4',   'name' => 'DOLOR ABDOMINAL'],
            ['code' => 'R80',     'name' => 'PROTEINURIA'],

            // 226-244: Traumatismos / External causes
            ['code' => 'T20, T32', 'name' => 'QUEMADURAS'],
            ['code' => 'T36, T50', 'name' => 'ENVENEN. POR DROG. Y OTRAS SUST.'],
            ['code' => 'T14.1',   'name' => 'HERIDAS'],
            ['code' => 'T14.2',   'name' => 'FRACTURAS'],
            ['code' => 'T14.3',   'name' => 'LUXACIONES Y ESGUINCES'],
            ['code' => 'T60',     'name' => 'INTOXICACION POR PLAGUICIDAS'],
            ['code' => 'T15, T19', 'name' => 'CUERPO EXTRAÑO EN ORIFICO NATURAL'],
            ['code' => 'S05',     'name' => 'TRAUMA OCULAR'],
            ['code' => 'T14.8',   'name' => 'OTROS TRAUMATISMOS'],
            ['code' => 'V01, V89', 'name' => 'ACCID. TRANSPORT. TERRESTRE'],
            ['code' => 'X21, X27, X29', 'name' => 'PICADURA DE INSEC. Y OTROS ANIM.'],
            ['code' => 'X20, W59', 'name' => 'EMPOZOÑAMIENTO OFÍDICO'],
            ['code' => 'A82',     'name' => 'MORDEDURAS SOSPECHOSAS DE RABIA'],
            ['code' => null,      'name' => 'OTROS ACCIDENTES'],
            ['code' => null,      'name' => 'ACCIDENTES DEL HOGAR'],
            ['code' => null,      'name' => 'ACCIDENTES LABORALES'],
            ['code' => 'Y40, Y57', 'name' => 'EFECTOS ADVERSOS DE MEDICAMENTOS'],
            ['code' => 'Y58, Y59', 'name' => 'EFECTOS ADVERSOS DE VACUNAS'],
            ['code' => 'X22',     'name' => 'ACULIADURA DE ALACRÁN'],

            // 245-268: Violencia familiar (sin código CIE)
            ['code' => null,      'name' => 'V. FLIAR. FÍSICA NIÑO (0 A 11 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. FÍSICA NIÑA (0 A 11 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. FÍSICA ADOLESCENTE HOMBRE (12 A 19 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. FÍSICA ADOLESCENTE MUJER (12 A 19 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. FÍSICA ADULTO (20 A 59 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. FÍSICA ADULTA (20 A 59 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. FÍSICA ADULTO MAYOR (60 AÑOS Y MAS)'],
            ['code' => null,      'name' => 'V. FLIAR. FÍSICA ADULTA MAYOR (60 AÑOS Y MAS)'],
            ['code' => null,      'name' => 'V. FLIAR. PSICOLÓGICA NIÑO (0 A 11 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. PSICOLÓGICA NIÑA (0 A 11 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. PSICOLÓGICA ADOLESCENTE HOMBRE (12 A 19 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. PSICOLÓGICA ADOLESCENTE MUJER (12 A 19 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. PSICOLÓGICA ADULTO (20 A 59 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. PSICOLÓGICA ADULTA (20 A 59 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. PSICOLÓGICA ADULTO MAYOR (60 AÑOS Y MAS)'],
            ['code' => null,      'name' => 'V. FLIAR. PSICOLÓGICA ADULTA MAYOR (60 AÑOS Y MAS)'],
            ['code' => null,      'name' => 'V. FLIAR. SEXUAL NIÑO (0 A 11 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. SEXUAL NIÑA (0 A 11 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. SEXUAL ADOLESCENTE HOMBRE (12 A 19 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. SEXUAL ADOLESCENTE MUJER (12 A 19 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. SEXUAL ADULTO (20 A 59 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. SEXUAL ADULTA (20 A 59 AÑOS)'],
            ['code' => null,      'name' => 'V. FLIAR. SEXUAL ADULTO MAYOR (60 AÑOS Y MAS)'],
            ['code' => null,      'name' => 'V. FLIAR. SEXUAL ADULTA MAYOR (60 AÑOS Y MAS)'],

            // 269-270: Otras causas (sin código CIE)
            ['code' => null,      'name' => 'OTRAS CAUSAS DE CONSULTA'],
            ['code' => null,      'name' => 'PACIENTES HOSPIT. O REFERIDOS PARA HOSPITALIZACIÓN'],
        ];

        $dataToInsert = array_map(function ($diag) use ($now) {
            return [
                'code'       => $diag['code'],
                'name'       => $diag['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $diagnoses);

        DB::table('sis_diagnoses')->insert($dataToInsert);
    }
}
