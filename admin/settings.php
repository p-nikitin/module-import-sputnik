<?php
/**
 * @global $APPLICATION CMain
 */

use Izifir\Sputnik\Option;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule('iblock');
Loader::includeModule('izifir.sputnik');

if (isset($_REQUEST['Settings']) && check_bitrix_sessid()) {
    $data = $_REQUEST['Settings'];

    foreach ($data as $option => $value) {
        Option::set('izifir.sputnik', $option, $value);
    }

    LocalRedirect($_SERVER['REQUEST_URI']);
}

if ($_REQUEST['run'] === 'y' && check_bitrix_sessid()) {
    \Izifir\Sputnik\Import::run();
}

$data = Option::getForModule('izifir.sputnik');

$iblockList = \Bitrix\Iblock\IblockTable::getList([
    'filter' => ['ACTIVE' => 'Y'],
    'select' => ['ID', 'NAME']
])->fetchAll();
$sectionList = [];
$propertyList = [];
if (!empty($data['iblock_id'])) {
    $sectionList = \Bitrix\Iblock\SectionTable::getList([
        'filter' => ['IBLOCK_ID' => $data['iblock_id'], 'DEPTH_LEVEL' => 1],
        'select' => ['ID', 'NAME']
    ])->fetchAll();

    $propertyList = \Bitrix\Iblock\PropertyTable::getList([
        'filter' => ['IBLOCK_ID' => $data['iblock_id']],
        'select' => ['ID', 'NAME']
    ])->fetchAll();
}

$fidProperties = \Izifir\Sputnik\Import::getFidProperties();

$tabs = array(
    array(
        'DIV' => 'main',
        'TAB' => 'Основные настройки',
        'TITLE' => 'Основные настройки'
    ),
    array(
        'DIV' => 'import',
        'TAB' => 'Импорт',
        'TITLE' => 'Ручной импорт объектов недвижимости'
    )
);

$tabControl = new CAdminTabControl("tabControl", $tabs, true, true);

$APPLICATION->SetTitle('Настройки импорта');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form method="POST" name="frm" id="frm" action="<?= POST_FORM_ACTION_URI ?>" enctype="multipart/form-data">
    <?= bitrix_sessid_post() ?>

    <?php
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>

    <tr>
        <td width="40%">URL адрес фида:</td>
        <td width="60%">
            <input type="text" name="Settings[fid_url]" value="<?= $data['fid_url'] ?>">
        </td>
    </tr>

    <tr>
        <td width="40%">Инфоблок для хранения объектов:</td>
        <td width="60%">
            <select name="Settings[iblock_id]">
                <option value="">Выберите из списка</option>
                <?php foreach ($iblockList as $iblock) : ?>
                    <option value="<?= $iblock['ID'] ?>"<?= $iblock['ID'] === $data['iblock_id'] ? ' selected' : '' ?>>
                        <?= $iblock['NAME'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>

    <tr>
        <td width="40%">Инфоблок для хранения сотрудников:</td>
        <td width="60%">
            <select name="Settings[employee_iblock_id]">
                <option value="">Выберите из списка</option>
                <?php foreach ($iblockList as $iblock) : ?>
                    <option value="<?= $iblock['ID'] ?>"<?= $iblock['ID'] === $data['employee_iblock_id'] ? ' selected' : '' ?>>
                        <?= $iblock['NAME'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>

    <?php if (!empty($data['iblock_id'])) : ?>
        <tr class="heading">
            <td colspan="2">Соответствие категорий</td>
        </tr>

        <tr>
            <td width="40%">Тип сделки "продажа":</td>
            <td width="60%">
                <select name="Settings[sale_section_id]">
                    <option value="">Выберите из списка</option>
                    <?php foreach ($sectionList as $section) : ?>
                        <option value="<?= $section['ID'] ?>"<?= $section['ID'] === $data['sale_section_id'] ? ' selected' : '' ?>>
                            <?= $section['NAME'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <td width="40%">Тип сделки "аренда":</td>
            <td width="60%">
                <select name="Settings[rent_section_id]">
                    <option value="">Выберите из списка</option>
                    <?php foreach ($sectionList as $section) : ?>
                        <option value="<?= $section['ID'] ?>"<?= $section['ID'] === $data['rent_section_id'] ? ' selected' : '' ?>>
                            <?= $section['NAME'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr class="heading">
            <td colspan="2">Соответствие полей</td>
        </tr>

        <?php foreach ($fidProperties as $tag => $fidProperty) : ?>
            <tr>
                <td width="40%"><?= $fidProperty ?> [<?= $tag ?>]:</td>
                <td width="60%">
                    <select name="Settings[<?= $tag ?>]">
                        <option value="">Выберите из списка</option>
                        <?php foreach ($propertyList as $property) : ?>
                            <option value="<?= $property['ID'] ?>"<?= $property['ID'] === $data[$tag] ? ' selected' : '' ?>>
                                <?= $property['NAME'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif ?>

    <?php $tabControl->BeginNextTab(); ?>

    <tr>
        <td colspan="2">
            <input type="submit" class="btn" value="Начать импорт">
        </td>
    </tr>

    <?php
    $tabControl->Buttons(array("disabled" => false, "back_url" => 'izifir_sputnik_settings.php?lang=' . LANGUAGE_ID));
    $tabControl->End();
    ?>
</form>
