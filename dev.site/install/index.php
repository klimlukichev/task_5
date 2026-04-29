<?

class dev_site extends CModule
{
    const MODULE_ID = 'dev.site';
    const AGENT_NAME = '\\Dev\\Site\\Agents\\Iblock::clearOldLogs();';

    public $MODULE_ID = 'dev.site',
        $MODULE_VERSION,
        $MODULE_VERSION_DATE,
        $MODULE_NAME = 'Тренировочный модуль',
        $PARTNER_NAME = 'dev';

    public function __construct()
    {
        $arModuleVersion = array();
        include __DIR__ . 'version.php';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
    }

    function InstallFiles($arParams = array())
    {
        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }

    protected function InstallAgents()
    {
        \CAgent::RemoveModuleAgents($this->MODULE_ID);

        return (bool)\CAgent::AddAgent(
            self::AGENT_NAME,
            $this->MODULE_ID,
            'N',
            3600,
            '',
            'Y',
            '',
            100
        );
    }

    protected function UnInstallAgents()
    {
        \CAgent::RemoveModuleAgents($this->MODULE_ID);
    }

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
        $this->InstallFiles();
        $this->InstallAgents();
    }

    public function DoUninstall()
    {
        $this->UnInstallAgents();
        UnRegisterModule($this->MODULE_ID);

        $this->UnInstallFiles();
    }

}
