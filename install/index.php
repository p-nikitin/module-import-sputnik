<?php

class izifir_sputnik extends CModule
{
    public $MODULE_ID = 'izifir.sputnik';

    public function __construct()
    {
        $arModuleVersion = [];

        include(__DIR__ . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = 'Импорт объектов недвижимости';
        $this->MODULE_DESCRIPTION = 'Импорт объектов недвижимости из CRM системы "Спутник"';

        $this->PARTNER_NAME = 'iZifir';
        $this->PARTNER_URI = 'https://izifir.ru';
    }

    public function InstallFiles()
    {
        CopyDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT']."/bitrix/admin", true, true);
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(__DIR__ . '/admin', $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
    }

    public function DoInstall()
    {
        $this->InstallFiles();
        RegisterModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);
    }


}