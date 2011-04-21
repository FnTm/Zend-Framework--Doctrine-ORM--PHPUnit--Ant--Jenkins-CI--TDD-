<?php
/**
 * Auth Login Controller
 *
 *
 * @author          Eddie Jaoude
 * @package       Auth Module
 *
 */
class Auth_LoginController extends Auth_BaseController
{

    /**
     * Initialisation method
     *
     * @author          Eddie Jaoude
     * @param           void
     * @return           void
     *
     */
    public function init()
    {
        parent::init();
    }
    
    /**
     * initiates before any action is dispatched
     *
     * @param	void
     * @return	void
     */
    public function preDispatch() {
        # if the user is logged in, they can not login again
        if (Zend_Auth::getInstance()->hasIdentity()) {
            # redirect login page
            $this->_helper->redirector('index', 'index', 'default');
        }
    }

    /**
     * default method
     *
     * @author          Eddie Jaoude
     * @param           void
     * @return           void
     *
     */
    public function indexAction() {
        # load form
        $this->loginForm = new Auth_Form_Login;

        $save = $this->authenticate();

        # send to view
        $this->view->loginForm = $save['form'];
        $this->view->alert = $save['alert'];
    }

    /**
     * authentication method
     *
     * @author          Eddie Jaoude
     * @param           void
     * @return           void
     *
     */
    public  function authenticate() {
        # get form
        $form = $this->loginForm;
        if ($this->_request->isPost()) {
            # get params
            $data = $this->_request->getPost();

            # check validate form
            if ($form->isValid($data)) {
                    # attempt to authentication
                    $authenticate = new Custom_Auth_Adapter($this->_em->getRepository('Auth_Model_Account'), $this->_auth->hash, $data);
                    $save = Zend_Auth::getInstance()->authenticate($authenticate);

                    if (Zend_Auth::getInstance()->hasIdentity()) {
                        # log user login - move to helper?
                        $account_login_statistic = new Auth_Model_AccountLoginStatistic;
                        $account_login_statistic->setAccount_id(Zend_Auth::getInstance()->getIdentity()->getId());
                        $account_login_statistic->setEvent('logged in');
                        $date = new Zend_Date;
                        $account_login_statistic->setCreated_at($date->toString('YYYY-MM-dd HH:mm:ss'));
                        $this->_em->persist($account_login_statistic);
                        $this->_em->flush();

                        # send to dashboard/user page
                        $this->_helper->redirector('index', 'account', 'auth');
                    } else {
                        $alert = 'Login failed: Invalid details'; // move to view
                    }
            } 
            # populate form
            $form->populate($data);
        }
        return array('form' => $form, 'alert' => empty($alert) ? null : $alert );
    }

    
    /**
     * Impersonate method
     *
     * @author          Eddie Jaoude
     * @param           void
     * @return           void
     *
     */
    public function impersonateAction() {
        
    }

}
