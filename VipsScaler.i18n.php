<?php
/**
 * Internationalisation file for extension VipsScaler.
 *
 * @file
 * @ingroup Extensions
 */
 
$messages = array();

$messages['en'] = array( 
	'vipstest' => 'VIPS scaling test page',

	'vipsscaler-desc' => 'Create thumbnails using VIPS',
	'vipsscaler-invalid-file' => 'Could not process requested file. Check that it exists on this wiki.',
	'vipsscaler-invalid-width' => 'Thumbnail width should be larger than zero and not larger than file width.',
	'vipsscaler-invalid-sharpen' => 'Sharpening amount should be a number larger than zero and smaller than five.',
	'vipsscaler-thumb-error' => 'VIPS could not generate a thumbnail with given parameters.',

	# Vipscaler test form:
	'vipsscaler-form-legend' => 'VIPS scaling',
	'vipsscaler-form-width'  => 'Thumbnail width:',
	'vipsscaler-form-file'   => 'File on this wiki:',
	'vipsscaler-form-sharpen-radius' => 'Amount of sharpening:',
	'vipsscaler-form-bilinear' => 'Bilinear scaling',
	'vipsscaler-form-submit' => 'Generate thumbnails',
		
	'vipsscaler-default-thumb' => 'Thumbnail generated with default scaler',
	'vipsscaler-vips-thumb' => 'Thumbnail generated with VIPS',
		
	'vipsscaler-show-both' => 'Show both thumbnails',
	'vipsscaler-show-default' => 'Show default thumbnail only',
	'vipsscaler-show-vips' => 'Show VIPS thumbnail only',

	# User rights
	'right-vipsscaler-test' => 'Use the VIPS scaling test interface [[Special:VipsTest]]',
);

/** Message documentation (Message documentation)
 * @author Purodha
 */
$messages['qqq'] = array(
	'vipstest' => 'Title of the Special:VipsTest page',
	'vipsscaler-desc' => '{{desc}}',
	'vipsscaler-invalid-file' => 'Error message when SpecialVipsTest was given a non existent or invalid file name',
	'vipsscaler-invalid-width' => 'Error message when SpecialVipsTest did not get a valid width parameter',
	'vipsscaler-thumb-error' => 'Error message when VIPS did not manage to generate a thumbnail',
	'vipsscaler-form-legend' => 'Special:VipsTest form: legend at top of the form',
	'vipsscaler-form-width' => 'Special:VipsTest form: label for the width input box',
	'vipsscaler-form-file' => 'Special:VipsTest form: label for the file input box',
	'vipsscaler-form-sharpen-radius' => 'Special:VipsTest form: label for the sharpening amount input box',
	'vipsscaler-form-bilinear' => 'Special:VipsTest form: Checkbox label to determine whether to enable bilinear scaling',
	'vipsscaler-form-submit' => 'Special:VipsTest form: submit button text. The page will then attempt to generate a thumbnail with the given parameters.',
	'vipsscaler-default-thumb' => 'Special:VipsTest: caption of the default thumbnail',
	'vipsscaler-vips-thumb' => 'Special:VipsTest: caption of the vips thumbnail',
	'vipsscaler-show-both' => 'Special:VipsTest: button to show both thumbnails',
	'vipsscaler-show-default' => 'Special:VipsTest: button to show default thumbnail only',
	'vipsscaler-show-vips' => 'Special:VipsTest: button to show VIPS thumbnail only',
);

/** Afrikaans (Afrikaans)
 * @author Naudefj
 */
$messages['af'] = array(
	'vipsscaler-desc' => 'Skep duimnaels met behulp van VIPS.',
);

/** Asturian (Asturianu)
 * @author Xuacu
 */
$messages['ast'] = array(
	'vipsscaler-desc' => 'Crear miniatures usando VIPS',
);

/** Belarusian (Taraškievica orthography) (‪Беларуская (тарашкевіца)‬)
 * @author Wizardist
 */
$messages['be-tarask'] = array(
	'vipsscaler-desc' => 'Стварае мініятуры з дапамогай VIPS',
);

/** Breton (Brezhoneg)
 * @author Fulup
 */
$messages['br'] = array(
	'vipsscaler-desc' => 'Krouiñ a ra munudoù en ur ober gant VIPS',
	'vipsscaler-form-file' => 'Restr er wiki-mañ :',
);

/** Bosnian (Bosanski)
 * @author CERminator
 */
$messages['bs'] = array(
	'vipsscaler-desc' => 'Pravljenje smanjenog pregleda koristeći VIPS',
);

/** Danish (Dansk)
 * @author Peter Alberti
 */
$messages['da'] = array(
	'vipstest' => 'Testside for skalering vha. VIPS',
	'vipsscaler-desc' => 'Opret miniaturebilleder ved hjælp af VIPS',
	'vipsscaler-invalid-file' => 'Ugyldig fil: kunne ikke behandle den angivne fil. Findes den på denne wiki?',
	'vipsscaler-invalid-width' => 'Du skal angive en bredde (heltal > 0).',
	'vipsscaler-thumb-error' => 'VIPS kunne ikke oprette et miniaturebillede med de angivne parametre.',
	'vipsscaler-form-legend' => 'Skalering vha. VIPS',
	'vipsscaler-form-width' => 'Miniaturebredde:',
	'vipsscaler-form-file' => 'Fil på denne wiki:',
	'vipsscaler-form-submit' => 'Opret miniature',
	'right-vipsscaler-test' => 'Brug brugerfladen til test af skalering ved hjælp af VIPS på [[Special:VipsTest]]',
);

/** German (Deutsch)
 * @author Kghbln
 */
$messages['de'] = array(
	'vipstest' => 'Testseite zur VIPS-Skalierung',
	'vipsscaler-desc' => 'Ermöglicht das Erstellen von Miniaturbildern mit VIPS',
	'vipsscaler-invalid-file' => 'Die angeforderte Datei konnte nicht verarbeitet werden. Bitte überprüfen, ob Sie auf diesem Wiki vorhanden ist.',
	'vipsscaler-invalid-width' => 'Die Breite des Miniaturbildes sollte größer als Null und nicht größer als die Breite des Bildes sein.',
	'vipsscaler-invalid-sharpen' => 'Der Wert der Bildschärfe sollte größer als Null und kleiner als Fünf sein.',
	'vipsscaler-thumb-error' => 'VIPS konnte auf Basis der angegebenen Parameter kein Miniaturbild generieren.',
	'vipsscaler-form-legend' => 'VIPS-Skalierung',
	'vipsscaler-form-width' => 'Breite des Miniaturbildes:',
	'vipsscaler-form-file' => 'Datei in diesem Wiki:',
	'vipsscaler-form-sharpen-radius' => 'Wert der Bildschärfe:',
	'vipsscaler-form-bilinear' => 'Bilineare Skalierung',
	'vipsscaler-form-submit' => 'Miniaturbild generieren',
	'vipsscaler-default-thumb' => 'Das Miniaturbild wurde mit dem Standardsaklierungsprogramm generiert.',
	'vipsscaler-vips-thumb' => 'Das Miniaturbild wurde mit VIPS generiert.',
	'vipsscaler-show-both' => 'Beide Miniaturbilder anzeigen',
	'vipsscaler-show-default' => 'Nur das Standardminiaturbild anzeigen',
	'vipsscaler-show-vips' => 'Nur das VIPS-Miniaturbild anzeigen',
	'right-vipsscaler-test' => 'Nutze das Testinterface zur VIPS-Skalierung [[Special:VipsTest]]',
);

/** German (formal address) (‪Deutsch (Sie-Form)‬)
 * @author Kghbln
 */
$messages['de-formal'] = array(
	'right-vipsscaler-test' => 'Nutzen Sie das Testinterface zur VIPS-Skalierung [[Special:VipsTest]]',
);

/** French (Français)
 * @author Gomoko
 * @author IAlex
 */
$messages['fr'] = array(
	'vipstest' => "Page de test de la mise à l'échelle de VIPS",
	'vipsscaler-desc' => "Créer des miniatures à l'aide de VIPS",
	'vipsscaler-invalid-file' => "Impossible de traiter le fichier demandé. Vérifiez qu'il existe sur ce wiki.",
	'vipsscaler-invalid-width' => 'La largeur de la vignette doit être supérieure à zéro et pas supérieure à la largeur du fichier.',
	'vipsscaler-invalid-sharpen' => 'La quantité de netteté doit être un nombre plus grand que zéro et plus petit que cinq.',
	'vipsscaler-thumb-error' => "VIPS n'a pas pu générer une miniature avec les paramètres fournis.",
	'vipsscaler-form-legend' => "Mise à l'échelle de VIPS",
	'vipsscaler-form-width' => 'Largeur de la miniature :',
	'vipsscaler-form-file' => 'Fichier sur ce wiki :',
	'vipsscaler-form-sharpen-radius' => 'Montant de netteté :',
	'vipsscaler-form-bilinear' => "Mise à l'échelle bilinéaire",
	'vipsscaler-form-submit' => 'Générer la vignette',
	'vipsscaler-default-thumb' => "Vignette générée avec une mise à l'échelle par défaut",
	'vipsscaler-vips-thumb' => 'Vignette générée avec VIPS',
	'vipsscaler-show-both' => 'Afficher les deux vignettes',
	'vipsscaler-show-default' => 'Afficher uniquement la vignette par défaut',
	'vipsscaler-show-vips' => 'Afficher uniquement la vignette VIPS',
	'right-vipsscaler-test' => "Utiliser l'interface de test de mise à l'échelle de VIP [[Special:VipsTest]]",
);

/** Franco-Provençal (Arpetan)
 * @author ChrisPtDe
 */
$messages['frp'] = array(
	'vipsscaler-desc' => 'Fât des figures avouéc VIPS.',
);

/** Galician (Galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'vipstest' => 'Páxina de probas da escala de VIPS',
	'vipsscaler-desc' => 'Crear miniaturas utilizando VIPS',
	'vipsscaler-invalid-file' => 'Non se puido procesar o ficheiro solicitado. Comprobe que existe neste wiki.',
	'vipsscaler-invalid-width' => 'O largo da miniatura debe ser maior que cero e non pode superar o largo do ficheiro.',
	'vipsscaler-thumb-error' => 'VIPS non pode xerar unha miniatura cos parámetros proporcionados.',
	'vipsscaler-form-legend' => 'Escala de VIPS',
	'vipsscaler-form-width' => 'Largo da miniatura:',
	'vipsscaler-form-file' => 'Ficheiro neste wiki:',
	'vipsscaler-form-submit' => 'Xerar a miniatura',
	'right-vipsscaler-test' => 'Utilizar a interface de probas de escala de VIPS, [[Special:VipsTest]]',
);

/** Hebrew (עברית)
 * @author Amire80
 */
$messages['he'] = array(
	'vipsscaler-desc' => 'יצירות תמונות ממוזערות באמצעות VIPS',
);

/** Upper Sorbian (Hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'vipsscaler-desc' => 'Přehladowe wobrazki z pomocu VIPS wutworić',
);

/** Interlingua (Interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'vipstest' => 'Pagina de test pro scalation VIPS',
	'vipsscaler-desc' => 'Crear miniaturas con VIPS',
	'vipsscaler-invalid-file' => 'Non poteva processar le file requestate. Verifica que illo existe in iste wiki.',
	'vipsscaler-invalid-width' => 'Le latitude del miniatura debe esser superior a zero e non superior al latitude del file.',
	'vipsscaler-invalid-sharpen' => 'Le quantitate de acutiamento debe esser un numero superior a zero e inferior a cinque.',
	'vipsscaler-thumb-error' => 'VIPS non poteva generar un miniatura con le parametros specificate.',
	'vipsscaler-form-legend' => 'Scalation VIPS',
	'vipsscaler-form-width' => 'Latitude del miniatura:',
	'vipsscaler-form-file' => 'File in iste wiki:',
	'vipsscaler-form-sharpen-radius' => 'Quantitate de acutiamento:',
	'vipsscaler-form-bilinear' => 'Redimensionamento bilinear',
	'vipsscaler-form-submit' => 'Generar miniatura',
	'vipsscaler-default-thumb' => 'Miniatura generate con redimensionator predefinite',
	'vipsscaler-vips-thumb' => 'Miniatura generate con VIPS',
	'vipsscaler-show-both' => 'Monstrar ambe miniaturas',
	'vipsscaler-show-default' => 'Monstrar miniatura predefinite solmente',
	'vipsscaler-show-vips' => 'Monstrar miniatura VIPS solmente',
	'right-vipsscaler-test' => 'Usar le interfacie de test pro scalation VIPS [[Special:VipsTest]]',
);

/** Indonesian (Bahasa Indonesia)
 * @author IvanLanin
 */
$messages['id'] = array(
	'vipsscaler-desc' => 'Membuat gambar mini dengan menggunakan VIPS',
);

/** Italian (Italiano)
 * @author Beta16
 */
$messages['it'] = array(
	'vipsscaler-desc' => 'Crea miniature utilizzando VIPS',
);

/** Japanese (日本語)
 * @author Schu
 */
$messages['ja'] = array(
	'vipsscaler-desc' => 'VIPS を用いてサムネイルを作成します。',
);

/** Colognian (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'vipsscaler-desc' => 'Minibeldsche met <i lang="en">VIPS</i> maache.',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'vipsscaler-desc' => 'Miniaturbiller mat VIPS maachen',
	'vipsscaler-form-file' => 'Fichier an dëser Wiki:',
);

/** Macedonian (Македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'vipstest' => 'Проба за менување на размер со VIPS',
	'vipsscaler-desc' => 'Создавање на минијатури со VIPS',
	'vipsscaler-invalid-file' => 'Не можев да ја обработам бараната податотека. Проверете дали воопшто постои на ова вики.',
	'vipsscaler-invalid-width' => 'Минијатурата мора да е поширока од нула, а потесна од изворната ширина на податотеката.',
	'vipsscaler-invalid-sharpen' => 'Изострувањето треба да е поголемо од нула а помало од пет.',
	'vipsscaler-thumb-error' => 'VIPS не можеше да создаде минијатура со зададените параметри.',
	'vipsscaler-form-legend' => 'Менување големина со VIPS',
	'vipsscaler-form-width' => 'Ширина на минијатурата:',
	'vipsscaler-form-file' => 'Податотека на ова вики:',
	'vipsscaler-form-sharpen-radius' => 'Изострување:',
	'vipsscaler-form-bilinear' => 'Билинеарно размерување',
	'vipsscaler-form-submit' => 'Создај минијатура',
	'vipsscaler-default-thumb' => 'Минијатура создадена со основно-зададениот размерител',
	'vipsscaler-vips-thumb' => 'Минијатура создадена со VIPS',
	'vipsscaler-show-both' => 'Прикажи ги двете минијатури',
	'vipsscaler-show-default' => 'Прикажи ја само основната минијатура',
	'vipsscaler-show-vips' => 'Прикажи ја само минијатурата од VIPS',
	'right-vipsscaler-test' => 'Употреба на го посредникот [[Special:VipsTest]] за испробување на менување големина со VIPS',
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 */
$messages['ms'] = array(
	'vipsscaler-desc' => 'Cipta gambar kenit dengan VIPS',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'vipstest' => 'Testpagina voor VIPS-transformaties',
	'vipsscaler-desc' => 'Miniaturen van bestanden aanmaken met VIPS',
	'vipsscaler-invalid-file' => 'Ongeldig bestand: het was niet mogelijk het gevraagde bestand te vewerken. Bestaat het niet binnen deze wiki?',
	'vipsscaler-invalid-width' => 'U moet een breedte opgeven (natuurlijk getal groter dan 0).',
	'vipsscaler-thumb-error' => 'VIPS kon geen miniatuur genereren met de opgegeven parameters.',
	'vipsscaler-form-legend' => 'VIPS-transformaties',
	'vipsscaler-form-width' => 'Breedte miniatuur:',
	'vipsscaler-form-file' => 'Bestand op deze wiki:',
	'vipsscaler-form-submit' => 'Miniatuur genereren',
	'right-vipsscaler-test' => 'Gebruik de [[Special:VipsTest|testinterface voor VIPS-transformaties]]',
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬)
 * @author Nghtwlkr
 */
$messages['no'] = array(
	'vipsscaler-desc' => 'Opprett miniatyrbilder med VIPS',
);

/** Polish (Polski)
 * @author Woytecr
 */
$messages['pl'] = array(
	'vipsscaler-desc' => 'Tworzy miniaturki korzystając z VIPS',
);

/** Piedmontese (Piemontèis)
 * @author Borichèt
 * @author Dragonòt
 */
$messages['pms'] = array(
	'vipsscaler-desc' => 'Creé dle miniadure dovrand VIPS',
);

/** Portuguese (Português)
 * @author Hamilton Abreu
 */
$messages['pt'] = array(
	'vipsscaler-desc' => 'Criar miniaturas usando VIPS',
);

/** Tarandíne (Tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'vipsscaler-desc' => 'Ccreje le miniature ausanne VIPS',
);

/** Russian (Русский)
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'vipsscaler-desc' => 'Создаёт миниатюры с помощью VIPS',
);

/** Slovenian (Slovenščina)
 * @author Dbc334
 */
$messages['sl'] = array(
	'vipsscaler-desc' => 'Ustvari sličice z VIPS',
);

/** Serbian Cyrillic ekavian (‪Српски (ћирилица)‬)
 * @author Rancher
 */
$messages['sr-ec'] = array(
	'vipsscaler-desc' => 'Прављење умањених приказа слика користећи VIPS',
);

/** Telugu (తెలుగు)
 * @author Veeven
 */
$messages['te'] = array(
	'vipsscaler-desc' => 'VIPSని ఉపయోగించి నఖచిత్రాలను తయారుచేయండి',
);

/** Tagalog (Tagalog)
 * @author AnakngAraw
 */
$messages['tl'] = array(
	'vipsscaler-desc' => 'Lumikha ng mga kagyat na ginagamit ang VIPS',
);

