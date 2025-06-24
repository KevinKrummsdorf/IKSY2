<?php
namespace App;

class HomeController
{
    private \Smarty $smarty;

    public function __construct(\Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    public function index(): void
    {
        // Flash anzeigen
        if (isset($_SESSION['flash'])) {
            $this->smarty->assign('flash', $_SESSION['flash']);
            unset($_SESSION['flash']);
        }

        // Optionales Modal (Login/Register)
        $show = $_GET['show'] ?? null;
        if (in_array($show, ['login', 'register'], true)) {
            $this->smarty->assign('show_modal', $show);
        }

        $this->smarty->display('index.tpl');
    }
}
