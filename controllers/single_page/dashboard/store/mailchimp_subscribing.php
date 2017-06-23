<?php
namespace Concrete\Package\CommunityStoreMailchimpSubscribing\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Support\Facade\Application;

class MailchimpSubscribing extends DashboardPageController
{
    public function view()
    {
        $app = Application::getFacadeApplication();
        $config = $app->make('config');
        $this->set('enableSubscriptions', $config->get('mailchimp_subscribing.enableSubscriptions'));
        $this->set('apiKey', $config->get('mailchimp_subscribing.apiKey'));
        $this->set('defaultListID', $config->get('mailchimp_subscribing.defaultListID'));
    }

    public function settings_saved()
    {
        $this->set('message', t('Settings Saved'));
        $this->view();
    }

    public function save_settings()
    {
        $app = Application::getFacadeApplication();
        $config = $app->make('config');

        if ($this->post()) {
            if ($this->token->validate('save_settings')) {
                $enableSubscriptions = $this->request->post('enableSubscriptions');
                $apiKey = $this->request->post('apiKey');
                $defaultListID = $this->request->post('defaultListID');

                if ($enableSubscriptions) {
                    if (!$apiKey) {
                        $this->error->add(t('An API Key is required'));
                    }
                }

                $config->save('mailchimp_subscribing.enableSubscriptions', $enableSubscriptions);
                $config->save('mailchimp_subscribing.apiKey', $apiKey);
                $config->save('mailchimp_subscribing.defaultListID', $defaultListID);

                if (!$this->error->has()) {
                    $this->redirect('/dashboard/store/mailchimp_subscribing', 'settings_saved');
                }
            } else {
                $this->error->add(t('Invalid CSRF token. Please refresh and try again.'));
                $this->view();
            }
        }
    }
}
