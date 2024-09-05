<?php

namespace Izifir\Sputnik;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyIndex\Manager;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;

class Import
{
    private static $_fidProperties = array(
        'type' => 'Тип сделки',
        'category' => 'Категория объекта',
        'commercial-type' => 'Категория коммерческого объекта',
        'commercial-building-type' => 'Тип здания, в котором находится объект',
        'purpose' => 'Рекомендуемое назначение объекта',
        'purpose-warehouse' => 'Назначение склада',
        'lot-number' => 'Номер лота',
        'url' => 'URL страницы с объявлением',
        'image' => 'Ссылка на изображение',
        'building-name' => 'Название жилого комплекса',
        'sales-agent' => 'Информация о продавце или арендодателе',
        'commission' => 'Размер комиссии для клиента в процентах',
        'organization' => 'Наименование юридического лица застройщика',
        'price' => 'Стоимость объекта',
        'rent-pledge' => 'Залог',
        'area' => 'Общая площадь',
        'floors-total' => 'Общее количество этажей в здании',
        'floor' => 'Этаж',
        'entrance-type' => 'Вход в помещение',
        'internet' => 'Наличие интернета',
        'cadastral-number' => 'Кадастровый номер объекта недвижимости',
        'creation-date' => 'Дата создания объявления',
        'vas' => 'Дополнительная услуга по продвижению объявления',
        'location' => 'Местоположение объекта',
        'country' => 'Страна, в которой расположен объект',
        'region' => 'Название субъекта РФ',
        'district' => 'Название района субъекта РФ',
        'locality-name' => 'Название населенного пункта',
        'sub-locality-name' => 'Район населенного пункта',
        'address' => 'Улица и номер дома',
        'apartment' => 'Номер помещения',
        'direction' => 'Шоссе',
        'distance' => 'Расстояние по шоссе до МКАД',
        'latitude' => 'Географическая широта',
        'longitude' => 'Географическая долгота',
        'metro' => 'Ближайшая станция метро',
        'railway-station' => 'Ближайшая железнодорожная станция',
        'deal-status' => 'Тип сделки',
        'utilities-included' => 'Коммунальные услуги включены в стоимость в договоре',
        'renovation' => 'Ремонт',
        'rooms' => 'Общее количество комнат',
        'room-furniture' => 'Наличие мебели',
        'air-conditioner' => 'Наличие системы кондиционирования',
        'fire-alarm' => 'Наличие пожарной сигнализации',
        'ventilation' => 'Наличие вентиляции',
        'water-supply' => 'Наличие водопровода',
        'sewerage-supply' => 'Наличие канализации',
        'electricity-supply' => 'Наличие электроснабжения',
        'electric-capacity' => 'Выделенная электрическая мощность',
        'gas-supply' => 'Подключение к газовым сетям',
        'floor-covering' => 'Покрытие пола',
        'heating-supply' => 'Наличие отопления',
        'window-type' => 'Тип окон',
        'window-view' => 'Вид из окон',
        'office-class' => 'Класс бизнес-центра',
        'ceiling-height' => 'Высота потолков в метрах',
        'guarded-building' => 'Закрытая территория',
        'access-control-system' => 'Наличие пропускной системы',
        'twenty-four-seven' => 'Возможность круглосуточного доступа сотрудников арендатора на объект аренды 7 дней в неделю',
        'lift' => 'Наличие лифта',
        'parking' => 'Наличие охраняемой парковки',
        'parking-places' => 'Количество предоставляемых парковочных мест',
        'parking-place-price' => 'Стоимость парковочного места',
        'parking-guest' => 'Наличие гостевых парковочных мест',
        'parking-guest-places' => 'Наличие гостевых парковочных мест',
        'security' => 'Наличие охраны',
        'eating-facilities' => 'Наличие предприятий общепита в здании',
        'is-elite' => 'Элитная недвижимость',
        'yandex-building-id' => 'Элитная недвижимость',
        'responsible-storage' => 'Ответственное хранение',
        'pallet-price' => 'Стоимость палето-места в месяц в рублях с учетом налогов',
        'freight-elevator' => 'Наличие грузового лифта',
        'truck-entrance' => 'Возможность подъезда фуры',
        'ramp' => 'Наличие пандуса',
        'railway' => 'Наличие ветки железной дороги',
        'office-warehouse' => 'Наличие офиса на складе',
        'open-area' => 'Наличие открытой площадки',
        'service-three-pl' => 'Наличие 3PL (логистических) услуг',
        'temperature-comment' => 'Комментарий про температурный режим на складе',
    );

    private static $_options = array();

    private static $_fidUrl = '';

    private static $_xml = null;

    private static $_iblockId = null;

    private static $_employeeIblockId = null;

    private static $_iblockProperties = [];

    private static $_commercialType = array(
        'auto repair' => 'Автосервис',
        'business' => 'Готовый бизнес',
        'free purpose' => 'Помещение своб. назн.',
        'hotel' => 'Гостиница',
        'land' => 'Комм. земельный участок',
        'legal address' => 'Юридический адрес',
        'manufacturing' => 'Производственное помещение',
        'office' => 'Офисные помещения',
        'public catering' => 'Общепит',
        'retail' => 'Торговое помещение',
        'warehouse' => 'Склад',
    );

    private static $_saleSectionId = null;

    private static $_rentSectionId = null;

    public static function run():void
    {
        Loader::includeModule('iblock');
        Loader::includeModule('highloadblock');

        self::loadOptions();
        self::loadIblockProperties();
        self::loadFile();

        $ids = [];

        if (!empty(self::$_xml)) {
            foreach (self::$_xml->offer as $offer) {
                $object = self::prepareOffer($offer);
                $select = ['ID', 'IBLOCK_ID'];
                $current = \CIBlockElement::GetList(
                    [],
                    ['XML_ID' => $object['XML_ID'], 'IBLOCK_ID' => self::$_iblockId],
                    false,
                    false,
                    $select
                )->Fetch();

                $element = new \CIBlockElement();

                if (!$current) {
                    $ids[] = $element->Add($object);
                } else {
                    $imagesIblockProperty = self::$_iblockProperties[self::$_options['image']];
                    if ($imagesIblockProperty) {
                        $images = \CIBlockElement::GetProperty($current['IBLOCK_ID'], $current['ID'], [], ['CODE' => $imagesIblockProperty['CODE']]);
                        while ($image = $images->Fetch()) {
                            $file = \CFile::MakeFileArray(\CFile::GetPath($image['VALUE']));
                            $file['del'] = 'Y';
                            $object['PROPERTY_VALUES'][$imagesIblockProperty['CODE']][$image['PROPERTY_VALUE_ID']] = $file;
                        }
                    }
                    $element->Update($current['ID'], $object);
                    $ids[] = $current['ID'];
                }

                if (!empty($ids)) {
                    $elementsIterator = ElementTable::query()
                        ->setFilter(['!ID' => $ids, 'ACTIVE' => 'Y', 'IBLOCK_ID' => self::$_iblockId])
                        ->setSelect(['ID'])
                        ->exec();

                    while ($el = $elementsIterator->fetch()) {
                        $element->Update($el['ID'], ['ACTIVE' => 'N']);
                    }
                }
            }

            $facetIndex = Manager::createIndexer(self::$_iblockId);
            $facetIndex->startIndex();
            $facetIndex->continueIndex();
            $facetIndex->endIndex();

            if (method_exists('\CIBlock', 'clearIblockTagCache')) {

                \CIBlock::enableClearTagCache();

                \CIBlock::clearIblockTagCache(self::$_iblockId);

                \CIBlock::DisableClearTagCache();
            }

            BXClearCache(true, "/iblock/catalog/");

            $staticHtmlCache = \Bitrix\Main\Data\StaticHtmlCache::getInstance();
            $staticHtmlCache->deleteAll();

            Manager::checkAdminNotification();
            \CBitrixComponent::clearComponentCache('bitrix:catalog.smart.filter');
        }
    }

    public static function getFidProperties()
    {
        return self::$_fidProperties;
    }

    public static function getOptions()
    {
        if (empty(self::$_options)) {
            self::loadOptions();
        }

        return self::$_options;
    }

    public static function getFidUrl(): string
    {
        return self::$_fidUrl;
    }

    private static function loadOptions(): void
    {
        self::$_options = Option::getForModule('izifir.sputnik');
        self::$_fidUrl = self::$_options['fid_url'];
        self::$_iblockId = self::$_options['iblock_id'];
        self::$_employeeIblockId = self::$_options['employee_iblock_id'];
        self::$_saleSectionId = self::$_options['sale_section_id'];
        self::$_rentSectionId = self::$_options['rent_section_id'];
        unset(
            self::$_options['fid_url'],
            self::$_options['iblock_id'],
            self::$_options['sale_section_id'],
            self::$_options['rent_section_id'],
            self::$_options['employee_iblock_id']
        );
    }

    private static function loadIblockProperties(): void
    {
        if (empty(self::$_iblockProperties)) {
            $filter = [];
            $enumFilter = [];
            foreach (self::$_fidProperties as $property => $name) {
                if (!empty(self::$_options[$property])) {
                    $filter['ID'][$property] = self::$_options[$property];
                }
            }

            if (!empty($filter)) {
                $propertyIterator = PropertyTable::query()
                    ->setFilter($filter)
                    ->setSelect(['*', 'ID', 'NAME', 'PROPERTY_TYPE', 'CODE'])
                    ->exec();

                while ($property = $propertyIterator->fetch()) {
                    if ($property['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST) {
                        $enumFilter['PROPERTY_ID'][$property['ID']] = $property['ID'];
                    }

                    self::$_iblockProperties[$property['ID']] = $property;
                }
            }

            if (!empty($enumFilter)) {
                $enumIterator = PropertyEnumerationTable::query()
                    ->setFilter($enumFilter)
                    ->setSelect(['ID', 'PROPERTY_ID', 'VALUE', 'XML_ID'])
                    ->exec();

                while ($enum = $enumIterator->fetch()) {
                    self::$_iblockProperties[$enum['PROPERTY_ID']]['ITEMS'][$enum['XML_ID']] = $enum;
                }
            }
        }
    }

    private static function loadFile()
    {
        if (!empty(self::$_fidUrl)) {
            $xml = file_get_contents(self::$_fidUrl);
            if (!empty($xml)) {
                self::$_xml = simplexml_load_string($xml);
            }
        }
    }

    private static function prepareOffer($offer)
    {
        $name = self::getObjectName($offer);
        $xmlId = self::getValue($offer->attributes()['internal-id']);
        $result = array(
            'NAME' => $name,
            'IBLOCK_ID' => self::$_iblockId,
            'IBLOCK_SECTION_ID' => mb_strtolower(self::getValue($offer->type)) === 'продажа' ? self::$_saleSectionId : self::$_rentSectionId,
            'XML_ID' => $xmlId,
            'ACTIVE' => 'Y',
            'CODE' => \CUtil::translit($name . '-' . $xmlId, 'ru', array(
                'replace_space' => '-',
                'replace_other' => '-',
            ))
        );



        if ($offer->image) {
            foreach ((array)$offer->image as $k => $image) {
                if ($k === 0) {
                    $result['PREVIEW_PICTURE'] = $result['DETAIL_PICTURE'] = \CFile::MakeFileArray($image);
                } elseif (!empty(self::$_options['image'])) {
                    $iblockProperty = self::$_iblockProperties[self::$_options['image']];
                    $result['PROPERTY_VALUES'][$iblockProperty['CODE']][] = \CFile::MakeFileArray($image);
                }
            }
        }

        $totalArea = null;
        $fullPrice = null;

        foreach ($offer as $property => $value) {
            if ($property === 'description') {
                $result['DETAIL_TEXT'] = self::getValue($value);
                $result['DETAIL_TEXT_TYPE'] = 'html';
            } elseif ($property === 'creation-date') {
                $result['ACTIVE_FROM'] = \Bitrix\Main\Type\DateTime::createFromPhp(date_create(self::getValue($value)));
            } elseif ($property === 'area' && !empty(self::$_options['area'])) {
                $iblockProperty = self::$_iblockProperties[self::$_options[$property]];
                if ($iblockProperty) {
                    $result['PROPERTY_VALUES'][$iblockProperty['CODE']] = (float)$value->value;
                }
                $totalArea = (float)$value->value;
            } elseif ($property === 'price') {
                $iblockProperty = self::$_iblockProperties[self::$_options[$property]];
                if ($iblockProperty) {
                    $result['PROPERTY_VALUES'][$iblockProperty['CODE']] = (float)$value->value;
                }
                $fullPrice = (float)$value->value;
            } elseif ($property === 'location') {
                if (!empty(self::$_options['locality-name']) && $value->{'locality-name'}) {
                    $iblockProperty = self::$_iblockProperties[self::$_options['locality-name']];
                    if ($iblockProperty) {
                        $result['PROPERTY_VALUES'][$iblockProperty['CODE']] = self::getValue($value->{'locality-name'});
                    }
                }

                if (!empty(self::$_options['address']) && $value->address) {
                    $iblockProperty = self::$_iblockProperties[self::$_options['address']];
                    if ($iblockProperty) {
                        $result['PROPERTY_VALUES'][$iblockProperty['CODE']] = self::getValue($value->address);
                    }
                }
                if (!empty(self::$_options['latitude'])) {
                    $iblockProperty = self::$_iblockProperties[self::$_options['latitude']];
                    if ($iblockProperty) {
                        $result['PROPERTY_VALUES'][$iblockProperty['CODE']] = self::getValue($value->latitude);
                    }
                }

                if (!empty(self::$_options['longitude'])) {
                    $iblockProperty = self::$_iblockProperties[self::$_options['longitude']];
                    if ($iblockProperty) {
                        $result['PROPERTY_VALUES'][$iblockProperty['CODE']] = self::getValue($value->longitude);
                    }
                }
            } elseif ($property === 'image') {
                // nothing to do
            } elseif ($property === 'sales-agent') {
                $iblockProperty = self::$_iblockProperties[self::$_options[$property]];

                if ($iblockProperty) {
                    $employeeId = self::getEmployeeId($value);
                    if ($employeeId) {
                        $result['PROPERTY_VALUES'][$iblockProperty['CODE']] = $employeeId;
                    }
                }
            } else {
                if (!empty(self::$_options[$property])) {
                    $iblockProperty = self::$_iblockProperties[self::$_options[$property]];
                    if ($iblockProperty['PROPERTY_TYPE'] === 'S' && $iblockProperty['USER_TYPE'] === 'directory') {
                        $hlTable = HighloadBlockTable::query()
                            ->setFilter(['TABLE_NAME' => $iblockProperty['USER_TYPE_SETTINGS_LIST']['TABLE_NAME']])
                            ->setSelect(['*'])
                            ->exec()
                            ->fetch();

                        if ($hlTable) {
                            $hlClass = HighloadBlockTable::compileEntity($hlTable)->getDataClass();

                            $item = $hlClass::query()
                                ->setFilter(['UF_XML_ID' => (string)$value])
                                ->setSelect(['ID', 'UF_XML_ID'])
                                ->exec()
                                ->fetch();

                            if ($item) {
                                $result['PROPERTY_VALUES'][$iblockProperty['CODE']] = $item['UF_XML_ID'];
                            }
                        }

                    } elseif ($iblockProperty['PROPERTY_TYPE'] === 'L') {
                        $item = $iblockProperty['ITEMS'][(string)$value]['ID'];
                        if (!$item) {
                            $item = current(array_filter($iblockProperty['ITEMS'], static function ($prop) use ($value) {
                                return mb_strtolower((string)$value) === mb_strtolower($prop['VALUE']);
                            }))['ID'];
                        }

                        if ($item) {
                            $result['PROPERTY_VALUES'][$iblockProperty['CODE']] = $item;
                        }
                    } else {
                        $result['PROPERTY_VALUES'][$iblockProperty['CODE']] = (string)$value;
                    }
                }
            }
        }

        if ($totalArea > 0 && $fullPrice > 0) {
            $meterPrice = (int)round($fullPrice / $totalArea, 2);
            $result['PROPERTY_VALUES']['METER_PRICE'] = $meterPrice;
        }

        return $result;
    }

    private static function getObjectName($offer): string
    {
        $name = '';
        $floor = null;
        $category = self::getValue($offer->category);

        if ($category === 'коммерческая') {
            $name .= self::$_commercialType[(string)$offer->{'commercial-type'}];
        }

        $area = $offer->area;
        if ($area) {
            if ($area->value) {
                $areaValue = self::getValue($area->value);
            }

            if ($area->unit) {
                $areaUnit = self::getValue($area->unit);
            }

            if (!empty($areaValue) && !empty($areaUnit)) {
                $name .= ', ' . $areaValue;

                if ($areaUnit === 'сотка') {
                    $name .= ' сот.';
                } else {
                    $name .= ' ' . $areaUnit;
                }
            }

            if ($offer->floor) {
                $floor = ', ' . (string)$offer->floor;
            }

            if ($floor) {
                if ($offer->{'floors-total'}) {
                    $floor .= '/' . (string)$offer->{'floors-total'} . ' эт.';
                } else {
                    $floor .= ' эт.';
                }

                $name .= ' ' . $floor;
            }
        }

        return $name;
    }

    private static function getValue($value): string
    {
        return (string)$value;
    }

    private static function getEmployeeId($data): mixed
    {
        if (!empty(self::$_employeeIblockId)) {
            $name = self::getValue($data->name);
            $phone = self::getValue($data->phone);
            $email = self::getValue($data->email);
            $photo = self::getValue($data->photo);

            $employee = \CIBlockElement::GetList(
                [],
                ['IBLOCK_ID' => self::$_employeeIblockId, 'PROPERTY_EMAIL' => $email],
                false,
                false,
                ['ID', 'IBLOCK_ID']
            )->Fetch();

            $element = new \CIBlockElement();

            if (!$employee) {
                $employeeId = $element->Add([
                    'ACTIVE' => 'Y',
                    'IBLOCK_ID' => self::$_employeeIblockId,
                    'NAME' => $name,
                    'CODE' => \CUtil::translit($name, 'ru'),
                    'PREVIEW_PICTURE' => !empty($photo) ? \CFile::MakeFileArray($photo) : false,
                    'PROPERTY_VALUES' => [
                        'EMAIL' => $email,
                        'PHONE' => $phone
                    ]
                ]);
            } else {
                $employeeId = $employee['ID'];
            }

            return $employeeId;
        }
        
        return null;
    }
}
