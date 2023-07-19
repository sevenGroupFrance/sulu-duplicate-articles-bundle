<?php

declare(strict_types=1);

namespace SevenGroupFrance\SuluDuplicateArticlesBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ListViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\ArticleBundle\Admin\ArticleAdmin;

class DuplicateFormAdmin extends Admin
{
    public function configureViews(ViewCollection $viewCollection): void
    {
        $logFilePath = '../public/view_names.log';

        $logContent = '';
        foreach ($viewCollection->all() as $name => $view) {
            $logContent .= $name . "\n";
        }
    
        file_put_contents($logFilePath, $logContent);
        if ($viewCollection->has('sulu_form.list')) {
            /** @var ListViewBuilderInterface $contentListViewBuilder */
            $contentListViewBuilder = $viewCollection->get('sulu_form.list');

            // Ajoute l'action de la barre d'outils pour la duplication
            $contentListViewBuilder->addToolbarActions([
                new ToolbarAction('Dupliquer_form', ['disable_for_empty_selection' => true]),
            ]);
        }
    }

    public static function getPriority(): int
    {
        return ArticleAdmin::getPriority() - 1;
    }
}