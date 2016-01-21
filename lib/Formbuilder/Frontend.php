<?php

/*

  Copyright (c) 2011, alexandre delattre
  All rights reserved.

  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions are met:

 * Redistributions of source code must retain the above copyright notice,
  this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright
  notice, this list of conditions and the following disclaimer in the
  documentation and/or other materials provided with the distribution.
 * Neither the name of grafyweb.com nor the names of its contributors may
  be used to endorse or promote products derived from this software
  without specific prior written permission.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
  AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
  IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
  ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
  LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
  SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
  INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
  CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
  ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
  POSSIBILITY OF SUCH DAMAGE.

 */

class Formbuilder_Frontend {

    protected $languages = null;

    protected static $defaultFormClass = 'Zend_Form';

    protected $formClass = 'Zend_Form';

    public static function setDefaultFormClass($defaultFormClass) {
        self::$defaultFormClass = $defaultFormClass;
    }

    public static function getDefaultFormClass() {
        return self::$defaultFormClass;
    }

    public function setFormClass($formClass) {
        $this->formClass = (string)$formClass;
    }

    public function getFormClass() {
        if(null !== $this->formClass) {
            return $this->formClass;
        } else {
            return self::getDefaultFormClass();
        }
    }

    protected function getLanguages() {
        if ($this->languages == null) {
            $languages = Pimcore_Tool::getValidLanguages();
            $this->languages = $languages;
        }
        return $this->languages;
    }

    /**
     *
     * @param string $name
     * @param string $locale
     * return Zend_Form
     */
    protected function getStaticForm($id, $locale, $className = 'Zend_Form') {
        if (file_exists(PIMCORE_PLUGINS_PATH . "/Zendformbuilder/data/form/form_" . $id . ".ini")) {
            $config = new Zend_Config_Ini(PIMCORE_PLUGINS_PATH . "/Zendformbuilder/data/form/form_" . $id . ".ini", 'config');

            $form = $this->createInstance($config->form, $className);
            $this->initTranslation($form, $id, $locale);

            return $form;
        } else {
            return false;
        }
    }

    protected function getDynamicForm($id, $locale, $className = 'Zend_Form') {

        if (file_exists(PIMCORE_PLUGINS_PATH . "/Zendformbuilder/data/main_" . $id . ".json")) {
            $config = new Zend_Config_Json(PIMCORE_PLUGINS_PATH . "/Zendformbuilder/data/main_" . $id . ".json");

            $datas = $config->toArray();

            $builder = new Formbuilder_Builder();
            $builder->setDatas($datas);
            $builder->setLocale($locale);
            $array = $builder->buildDynamicForm();

            $form = $this->createInstance($array, $className);
            $this->initTranslation($form, $id, $locale);

            return $form;
        } else {
            return false;
        }
    }

    protected function createInstance($config, $className = 'Zend_Form') {
        $reflClass = new ReflectionClass($className);
        if(!($reflClass->isSubclassOf('Zend_Form') || $reflClass->name == 'Zend_Form')) {
            throw new Exception('Form class must be a subclass of "Zend_Form"');
        }
        return $reflClass->newInstance($config);
    }

    protected function initTranslation(\Zend_Form $form, $id, $locale = null) {

        if($locale === null) {
            $locale = \Zend_Locale::findLocale();
        }

        $trans = $this->translateForm($id, $locale);

        if ($locale != null && $locale != "") {
            if(null === $form->getTranslator()) {
                $form->setTranslator($trans);
            } else {
                $form->getTranslator()->addTranslation($trans);
            }
        }
    }

    public function getTwitterForm($name, $locale = null,$horizontal=true) {
        $this->getLanguages();

        $table = new Formbuilder_Formbuilder();
        $id = $table->getIdByName($name);


        if (is_numeric($id) == true) {

            if (file_exists(PIMCORE_PLUGINS_PATH . "/Zendformbuilder/data/form/form_" . $id . ".ini")) {
                $config = new Zend_Config_Ini(PIMCORE_PLUGINS_PATH . "/Zendformbuilder/data/form/form_" . $id . ".ini", 'config');


                $trans = $this->translateForm($id, $locale);

                Zend_Form::setDefaultTranslator($trans);

                if($horizontal==true){
                    $form = new Twitter_Bootstrap_Form_Horizontal($config->form);
                }else{
                    $form = new Twitter_Bootstrap_Form_Vertical($config->form);
                }

                $form->setDisableTranslator(true);
                if ($locale != null && $locale != "") {

                    $form->setTranslator($trans);
                }


                return $form;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * If $dynamic equal true, the form form is completly rebuild. It is useful if you need to interact to the form with hooks.
     *
     * @param string $name
     * @param string $locale
     * @param boolean $dynamic
     * @param string Custom form class
     * @return Zend_Form
     */
    public function getForm($name, $locale=null, $dynamic=false, $formClass = null) {
        $this->getLanguages();

        $table = new Formbuilder_Formbuilder();
        $id = $table->getIdByName($name);

        if (is_numeric($id) == true) {

            $class = $formClass ?: $this->getFormClass();
            if ($dynamic == false) {
                $form = $this->getStaticForm($id, $locale, $class);
            } else {
                $form = $this->getDynamicForm($id, $locale, $class);
            }

            return $form;
        } else {
            return false;
        }
    }

    protected function translateForm( $id, $locale) {/* @var $form Zend_Form */

        $trans = new Zend_Translate_Adapter_Csv(array("delimiter" => ",", "disableNotices" => true));




        $file = PIMCORE_PLUGINS_PATH . "/Zendformbuilder/data/lang/form_" . $id . "_" . $locale . ".csv";
        if (file_exists($file)) {
            $trans->addTranslation(
                    array(
                        'content' => $file,
                        'locale' => $locale
            ));
        }


        $file = PIMCORE_PLUGINS_PATH . "/Zendformbuilder/data/lang/errors/" . $locale . "/Zend_Validate.php";
        if (file_exists($file)) {
            $arrTrans = new Zend_Translate_Adapter_Array(array("disableNotices" => true));
            $arrTrans->addTranslation(array("content" => $file, "locale" => $locale));
            $trans->addTranslation($arrTrans);
        }



        return $trans;
    }

}

?>
