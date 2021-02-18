<?php  

/**  
 * @file  
 * Contains Drupal\content_lifecycle\Form\MessagesForm.  
 */  

namespace Drupal\content_lifecycle\Form;  

use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  

class MessagesForm extends ConfigFormBase {  
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {  
    return [  
      'content_lifecycle.adminsettings',  
    ];  
  }  

  /**  
   * {@inheritdoc}  
   */  
  public function getFormId() {  
    return 'content_lifecycle_form';  
  }  
  

/**  
   * {@inheritdoc}  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {  
    $config = $this->config('content_lifecycle.adminsettings');  
    $terms = $this ->getpageowners();

    $options = [];
     foreach ($terms as $tid => $term){
       $options[$tid] = $term -> getName();
     }
    #kint($options); 

    $form['example_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Page Owner'),
      '#options' => [$options],
      '#description' => $this->t('Set the pages to a selected Page Owner'),  
      
      ];

    $form['content_lifecycle_message'] = [  
      '#type' => 'textarea',  
      '#title' => $this->t('Page nids *'),  
      '#description' => $this->t('Enter nids, one per line, of page to set to this page owner. The script will update both english and french versions to the same owner.'),  
      '#default_value' => $config->get('content_lifecycle_message'),  
    ];  

    

      $form['copy'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Only Update pages without a Page Owner'),
        '#description' => $this->t('If checked then the page owner value of a page will only be set if its currently not set. Uncheck to force the pages to update no matter what.'),  
      );

    return parent::buildForm($form, $form_state);  
  }  

  /**  
   * {@inheritdoc}  
   */  
  public function submitForm(array &$form, FormStateInterface $form_state) {  
    parent::submitForm($form, $form_state);  

    $this->config('content_lifecycle.adminsettings')  
      ->set('content_lifecycle_message', $form_state->getValue('content_lifecycle_message'))  
      ->save();  
  }  

private function getpageowners(){
  
  $query = \Drupal::entityQuery('taxonomy_term');
  $query->condition('vid', "page_owners");
  $query->sort('weight');
  $tids = $query->execute();
  $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
  #kint($terms);

return $terms; 

}




}