<?php

/*
 * This file is part of the `All4One/Ujallas.hu` project.
 *
 * (c) https://ujallas.hu
 *
 * Developed by: Ferencz Dávid Tamás <fdt0712@gmail.com>
 * Contributed: Sipos Zoltán <sipiszoty@gmail.com>, Pintér Szilárd <leaderlala00@gmail.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BackOffice\AppBundle\Admin\Offer;

use All4One\AppBundle\Form\Type\CustomFormType;
use BackOffice\AppBundle\Admin\AbstractAdmin;
use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Entity\Dictionary\DicAdvantage;
use Common\CoreBundle\Entity\Dictionary\DicCategory;
use Common\CoreBundle\Entity\Dictionary\DicDetail;
use Common\CoreBundle\Entity\Dictionary\DicDrivingLicense;
use Common\CoreBundle\Entity\Dictionary\DicEducationLevel;
use Common\CoreBundle\Entity\Dictionary\DicExpectation;
use Common\CoreBundle\Entity\Dictionary\DicExperience;
use Common\CoreBundle\Entity\Dictionary\DicExperienceLevel;
use Common\CoreBundle\Entity\Dictionary\DicItExperience;
use Common\CoreBundle\Entity\Dictionary\DicItExperienceLevel;
use Common\CoreBundle\Entity\Dictionary\DicJobForm;
use Common\CoreBundle\Entity\Dictionary\DicLanguage;
use Common\CoreBundle\Entity\Dictionary\DicLanguageLevel;
use Common\CoreBundle\Entity\Dictionary\DicPersonalStrength;
use Common\CoreBundle\Entity\Dictionary\DicShift;
use Common\CoreBundle\Entity\Dictionary\DicSoftwareExperience;
use Common\CoreBundle\Entity\Dictionary\DicSoftwareExperienceLevel;
use Common\CoreBundle\Entity\Dictionary\DicTask;
use Common\CoreBundle\Entity\Dictionary\Dictionary;
use Common\CoreBundle\Entity\Offer\OfferDictionaryRelation;
use Common\CoreBundle\Enumeration\Offer\OfferStatusEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Common\CoreBundle\Entity\Offer\Offer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


/**
 * Class OfferAdmin.
 */
class OfferAdmin extends AbstractAdmin
{
    /**
     * @var string
     */
    protected $baseRouteName = 'offer';

    /**
     * @param ShowMapper $showMapper
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->tab('Alapadatok')
            ->with('Bevezetés', ['class' => 'col-md-6'])
            ->add('firm')
            ->add('title')
            ->add('status')
            ->add('numberOfEmployee')
            ->add('anonim')
            ->add('lead')
            ->add('leadImg', null, [
                'label' => 'Hirdetés kép',
                'template' => '@SonataAdmin/CRUD/Common/offer_lead_img_show.html.twig',
            ])
            ->add('minimal_package')
            ->add('minimal_email')
            ->add('minimal_title')
            ->add('minimal_url')
            ->add('minimal_without_cv')
            ->end()
            ->end()

//            ->add('companyHelps', AutocompleteType::class, [
//                'descriptor' => 'dic_company_help.descriptor',
//                'label' => 'TODO companyHelps',
//                'multiple' => true,
//            ])
//            ->add('lifeStyles', AutocompleteType::class, [
//                'descriptor' => 'dic_life_style.descriptor',
//                'label' => 'TODO lifestyle',
//                'multiple' => true,
//            ])
//            ->add('marketStatuses', AutocompleteType::class, [
//                'descriptor' => 'dic_market_status.descriptor',
//                'label' => 'TODO marketstatus',
//                'multiple' => true
//            ])
        ;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('accept', $this->getRouterIdParameter().'/accept');
        $collection->add('deny', $this->getRouterIdParameter().'/deny');
        $collection->add('duplicate', $this->getRouterIdParameter().'/duplicate');
        $collection->add('delete_image', $this->getRouterIdParameter().'/delete-image');

    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {


        $isEdit = $this->getRequest()->get('id') !== null;

    $formMapper
        ->tab('Alapadatok')
        ->with('Bevezetés', ['class' => 'col-md-6'])
        ->add('firm', ModelAutocompleteType::class, [
            'property' => 'name',
            'to_string_callback' => function($entity, $property) {
                  return $entity->getNameWithContact();
            },
            'label' => 'Cég',
        ])
        ->add('title', null, [
            'label' => 'label.offer.manage.title',
            'required' => false,
        ]);

        // Alternatív megoldás modernebb JavaScript funkcióval:

        if ($isEdit && $this->getSubject() && $this->getSubject()->getSlug()) {
            $offerUrl = $this->generateFrontendUrl($this->getSubject());
            $uniqueId = 'offer-url-' . uniqid(); // Egyedi ID a JavaScript számára
            
            $formMapper->add('offerUrl', TextType::class, [
                'label' => 'Hirdetés URL',
                'mapped' => false,
                'disabled' => true,
                'data' => $offerUrl,
                'help' => '
                    <div style="margin-top: 5px;">
                        <div class="input-group">
                            <input type="text" 
                                   id="' . $uniqueId . '"
                                   value="' . $offerUrl . '" 
                                   readonly 
                                   class="form-control"
                                   style="background: #f9f9f9; cursor: pointer; font-size: 13px;" />
                            <span class="input-group-btn">
                                <button type="button" 
                                        onclick="copyToClipboard(\'' . $uniqueId . '\')"
                                        class="btn btn-default"
                                        title="URL másolása">
                                    <i class="fa fa-copy"></i>
                                </button>
                                <a href="' . $offerUrl . '" 
                                   target="_blank" 
                                   class="btn btn-primary">
                                    <i class="fa fa-external-link"></i> Megnyitás
                                </a>
                            </span>
                        </div>
                    </div>
                    <script>
                        function copyToClipboard(elementId) {
                            var copyText = document.getElementById(elementId);
                            copyText.select();
                            copyText.setSelectionRange(0, 99999);
                            document.execCommand("copy");
                            
                            // Visszajelzés
                            var button = copyText.parentElement.querySelector("button");
                            var originalHtml = button.innerHTML;
                            button.innerHTML = "<i class=\"fa fa-check\"></i> Másolva!";
                            button.classList.add("btn-success");
                            button.classList.remove("btn-default");
                            
                            setTimeout(function() {
                                button.innerHTML = originalHtml;
                                button.classList.remove("btn-success");
                                button.classList.add("btn-default");
                            }, 2000);
                        }
                    </script>
                ',
                'attr' => [
                    'style' => 'display: none;' // Az eredeti input elrejtése
                ]
            ]);
        }
    
    // Csak szerkesztési módban jelenjen meg az updateSlug checkbox
    if ($isEdit) {
        $formMapper->add('updateSlug', CheckboxType::class, [
            'label' => 'URL módosítása',
            'required' => false,
            'mapped' => false,
            'data' => false,
            'help' => 'Ha be van jelölve, a cím változtatásakor az URL is módosul.',
        ]);
    }

          $formMapper->add('status', EnumType::class, [
                'label' => 'label.status',
                'enum_class' => OfferStatusEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('categories', ModelAutocompleteType::class, [
                'property' => 'value',
                'class' => DicCategory::class,
                'label' => 'label.offer.manage.category',
                'multiple' => true,
            ], [
                'admin_code' => 'admin.dic_subcategory',
            ])
            /*
            ->add('locations', ModelAutocompleteType::class, [
                'property' => 'value',
                'class' => Dictionary::class,
                'label' => 'label.offer.manage.locations',
                'multiple' => true,
            ], [
                'admin_code' => 'admin.dic_location',
            ])*/
            ->add('locations', ModelAutocompleteType::class, [
                'property' => 'value',
                'class' => Dictionary::class,
                'label' => 'Hirdetés megjelenésének helyszíne(i)',
                'help' => 'XML-ek esetében ezek a települések szolgálnak a munkavégzés helyszíneként.',
                'multiple' => true,
                'attr' => [
                    'data-paste-handler' => true,
                    'class' => 'location-paste-handler'
                ]
            ], [
                'admin_code' => 'admin.dic_location'
            ])
            ->add('workLocations', ModelAutocompleteType::class, [
                'property' => 'value',
                'class' => Dictionary::class,
                'label' => 'Munkavégzés tényleges helyszíne(i)',
                'help' => 'Csak a mumi weboldalán, az álláshirdetés adatai résznél jelenik meg.',
                'multiple' => true,
                'attr' => [
                    'data-paste-handler' => true,
                    'class' => 'work-location-paste-handler'
                ]
            ], [
                'admin_code' => 'admin.dic_location'
            ])
            ->add('locationsText', TextareaType::class, [
                'label' => 'Települések szöveges megadása',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Pl.: Budapest, Debrecen, Szeged (vesszővel elválasztva)',
                    'class' => 'locations-text-input'
                ],
                'help' => 'Adja meg a településneveket vesszővel elválasztva. A rendszer automatikusan megkeresi és hozzárendeli a településeket a hirdetéshez.'
            ])
            ->add('numberOfEmployee', IntegerType::class, [
                'label' => 'label.offer.manage.number_of_employee',
                'attr' => ['placeholder' => 'label.offer.manage.number_of_employee.placeholder'],
            ])
            ->add('anonim', CheckboxType::class, [
                'label' => 'label.offer.manage.anonim',
                'required' => false,
            ]);

            // Hirdetés kép feltöltés - csak akkor jelenik meg, ha nincs még kép
if (!$isEdit || !$this->getSubject() || !$this->getSubject()->getLeadImg()) {
$formMapper->add('leadImg', FileType::class, [
    'label' => false,
    'mapped' => false,
    'required' => false,
    'data_class' => null,
    'constraints' => [
        new File([
            'maxSize' => '50M',
            'mimeTypes' => ['image/jpeg', 'image/png'],
            'mimeTypesMessage' => 'Csak JPEG és PNG képeket lehet feltölteni!',
            'maxSizeMessage' => 'A kép mérete nem lehet nagyobb 50MB-nál!',
        ])
    ],
    'attr' => [
        'accept' => 'image/jpeg,image/png',
        'style' => 'display: none;',
        'class' => 'hidden-file-input',
        'onchange' => 'handleFileSelect(this)'
    ],
    'help' => '
        <div style="margin-bottom: 10px; font-weight: bold; color: #333;">Hirdetés kép</div>
        <div id="upload-button" onclick="triggerFileInput()" style="
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            border: 3px solid transparent;
            border-radius: 20px;
            padding: 25px 40px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(243, 156, 18, 0.4);
            margin-bottom: 15px;
            box-sizing: border-box;
        " onmouseover="this.style.background=\'linear-gradient(135deg, #e67e22 0%, #d35400 100%)\'; this.style.transform=\'translateY(-3px) scale(1.02)\';" onmouseout="this.style.background=\'linear-gradient(135deg, #f39c12 0%, #e67e22 100%)\'; this.style.transform=\'none\';">
            📸 Kép feltöltése - Kattintson ide!
        </div>
        <div style="
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        ">
            🎯 JPEG, PNG formátum • 📏 Max 50MB • 🔧 Intelligens átméretezés: 1100×500px
        </div>
        <script>
        function triggerFileInput() {
            var fileInput = document.querySelector(".hidden-file-input");
            if (fileInput) {
                fileInput.click();
            }
        }
        
        function handleFileSelect(input) {
            var button = document.getElementById("upload-button");
            if (input.files && input.files[0]) {
                var fileName = input.files[0].name;
                var fileSize = Math.round(input.files[0].size / 1024 / 1024 * 100) / 100;
                
                button.innerHTML = "✅ Kiválasztva: " + fileName + " (" + fileSize + " MB)";
                button.style.background = "linear-gradient(135deg, #27ae60 0%, #229954 100%)";
                button.style.boxShadow = "0 10px 30px rgba(39, 174, 96, 0.4)";
                
                // Hover effektek módosítása
                button.onmouseover = function() {
                    this.style.background = "linear-gradient(135deg, #229954 0%, #1e8449 100%)";
                    this.style.transform = "translateY(-3px) scale(1.02)";
                };
                button.onmouseout = function() {
                    this.style.background = "linear-gradient(135deg, #27ae60 0%, #229954 100%)";
                    this.style.transform = "none";
                };
            }
        }
        </script>
    '
]);
}
           if ($isEdit && $this->getSubject() && $this->getSubject()->getLeadImg()) {
    $formMapper->add('currentLeadImg', TextType::class, [
        'label' => 'Jelenlegi kép',
        'mapped' => false,
        'disabled' => true,
        'data' => 'Kép feltöltve: ' . $this->getSubject()->getLeadImg(),
        'help' => '
            <div style="margin-top: 10px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                <div style="text-align: center;">
                    <a href="/' . $this->getSubject()->getLeadImg() . '" 
                       target="_blank" 
                       style="display: inline-block; text-decoration: none; transition: all 0.3s;">
                        <img src="/' . $this->getSubject()->getLeadImg() . '?v='.date('Ymdhis').'" 
                             style="max-width: 300px; max-height: 200px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s;" 
                             onmouseover="this.style.transform=\'scale(1.02)\'" 
                             onmouseout="this.style.transform=\'scale(1)\'" 
                             alt="Jelenlegi kép" />
                    </a>
                    <div style="margin-top: 10px;">
                        <a href="/' . $this->getSubject()->getLeadImg() . '" 
                           target="_blank" 
                           class="btn btn-sm btn-primary" 
                           style="margin-right: 10px;">
                            <i class="fa fa-external-link"></i> Nagyítás
                        </a>
                        <a href="' . $this->generateObjectUrl('delete_image', $this->getSubject()) . '" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm(\'Biztosan törli a képet?\')">
                            <i class="fa fa-trash"></i> Törlés
                        </a>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            Fájlnév: ' . $this->getSubject()->getLeadImg() . '
                        </small>
                    </div>
                </div>
            </div>
        ',
    ]);
}
            $formMapper->add('lead', TextareaType::class, [
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'label.offer.manage.lead.placeholder',
                    'class' => 'tinymce', // Ez az osztály aktiválja a TinyMCE-t
                    'data-theme' => 'minimal',
                ],
                'label' => 'label.offer.manage.lead',
                'required' => false,
            ])
            ->end()
            ->with('Szövegek', ['class' => 'col-md-6'])
            ->add('tasks', CollectionType::class, [
                'label' => 'Feladatok',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => DicTask::class,
                    'fields' => [
                        'value' => [
                            'type' => TextType::class,
                            'options' => [
                                'label' => 'label.offer.manage.task',
                                'attr' => ['placeholder' => 'label.offer.manage.task.placeholder'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
            ])
            ->add('details', CollectionType::class, [
                'label' => 'Kínálatok',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => DicDetail::class,
                    'fields' => [
                        'value' => [
                            'type' => TextType::class,
                            'options' => [
                                'label' => 'label.offer.manage.detail',
                                'attr' => ['placeholder' => 'label.offer.manage.detail.placeholder'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->add('advantages', CollectionType::class, [
                'label' => 'Előnyök',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => DicAdvantage::class,
                    'fields' => [
                        'value' => [
                            'type' => TextType::class,
                            'options' => [
                                'label' => 'label.offer.manage.advantage',
                                'attr' => ['placeholder' => 'label.offer.manage.advantage.placeholder'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
//            ->add('shifts', ModelAutocompleteType::class, [
//                'property' => 'value',
//                'label' => 'label.offer.manage.shifts',
//                'placeholder' => 'label.offer.manage.shifts.placeholder',
//                'multiple' => true,
//                'required' => true,
//            ], [
//                'admin_code' => 'admin.dic_shift',
//            ])
            ->end()
            ->end()
            ->tab('Elvárások és munka adatok')
            ->with('Munka adatai', ['class' => 'col-md-6'])
            ->add('shifts', EntityType::class, [
                'class' => DicShift::class,
                'choice_label' => 'value',
                'label' => 'label.offer.manage.shifts',
                'multiple' => true,
            ])
//            ->add('jobForms', ModelAutocompleteType::class, [
//                'property' => 'value',
//                'label' => 'label.offer.manage.job_forms',
//                'placeholder' => 'label.offer.manage.job_forms.placeholder',
//                'multiple' => true,
//                'required' => true,
//            ], [
//                'admin_code' => 'admin.dic_job_form',
//            ])
            ->add('jobForms', EntityType::class, [
                'class' => DicJobForm::class,
                'choice_label' => 'value',
                'label' => 'label.offer.manage.job_forms',
                'multiple' => true,
            ])
            ->add('drivingLicenses', EntityType::class, [
                'class' => DicDrivingLicense::class,
                'choice_label' => 'value',
                'label' => 'label.offer.manage.driving_licenses',
                'expanded' => false,
                'multiple' => true,
                'required' => false,
            ])
//            ->add('drivingLicenses', ModelAutocompleteType::class, [
//                'property' => 'value',
//                'label' => 'label.offer.manage.driving_licenses',
//                'placeholder' => 'label.offer.manage.driving_licenses.placeholder',
//                'multiple' => true,
//                'required' => false,
//            ], [
//                'admin_code' => 'admin.dic_driving_licence',
//            ])
//            ->add('personalStrengths', EntityType::class, [
//                'class' => DicPersonalStrength::class,
//                'choice_label' => 'value',
//                'label' => 'label.personal-strengths',
//                'expanded' => false,
//                'multiple' => true,
//                'required' => false,
//            ])
            ->end()
            ->end()
            ->tab('Elvárások és munka adatok')
            ->with('Elvárások', ['class' => 'col-md-6'])
            ->add('personalStrengths', ModelAutocompleteType::class, [
                'property' => 'value',
                'label' => 'label.offer.manage.personal_strengths',
                'class' => DicPersonalStrength::class,
                'placeholder' => 'label.offer.manage.personal_strengths.placeholder',
                'multiple' => true,
                'required' => false,
            ], [
                'admin_code' => 'admin.dic_personal_strength',
            ])
            ->end()
            ->with('Végzettségek', ['class' => 'col-md-6'])
            ->add('minEducation', EntityType::class, [
                'class' => DicEducationLevel::class,
                'choice_label' => 'value',
                'label' => 'label.offer.manage.min_education',
                'placeholder' => 'Kérem válasszon!',
                'multiple' => false,
                'expanded' => false,
            ], [
                'admin_code' => 'admin.dic_education_level',
            ])
            ->add('educations', CollectionType::class, [
                'label' => 'label.offer.manage.educations',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => OfferDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => EntityType::class,
                            'options' => [
                                'class' => DicCategory::class,
                                'choice_label' => 'value',
                                'label' => 'label.offer.manage.education',
                                'placeholder' => 'Kérem válasszon!',
                                'multiple' => false,
                                'expanded' => false,
                            ],
                        ],
                        'level' => [
                            'type' => EntityType::class,
                            'options' => [
                                'class' => DicEducationLevel::class,
                                'choice_label' => 'value',
                                'label' => 'label.offer.manage.education_level',
                                'placeholder' => 'Kérem válasszon!',
                                'multiple' => false,
                                'expanded' => false,
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->end()
            ->with('Tapasztalatok', ['class' => 'col-md-6'])
            ->add('experiences', CollectionType::class, [
                'label' => 'Tapasztalatok',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => OfferDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => EntityType::class,
                            'options' => [
                                'class' => DicExperience::class,
                                'choice_label' => 'value',
                                'label' => 'label.offer.manage.experience',
                                'placeholder' => 'Kérem válasszon!',
                                'multiple' => false,
                                'expanded' => false,
                            ],
                        ],
                        'level' => [
                            'type' => EntityType::class,
                            'options' => [
                                'class' => DicExperienceLevel::class,
                                'choice_label' => 'value',
                                'label' => 'label.offer.manage.experience_level',
                                'placeholder' => 'Kérem válasszon!',
                                'multiple' => false,
                                'expanded' => false,
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->end()
            ->with('IT ismeretek', ['class' => 'col-md-6'])
            ->add('itExperiences', CollectionType::class, [
                'label' => 'IT tapasztalatok',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => OfferDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => EntityType::class,
                            'options' => [
                                'class' => DicItExperience::class,
                                'choice_label' => 'value',
                                'label' => 'label.offer.manage.it_experience',
                                'placeholder' => 'Kérem válasszon!',
                                'multiple' => false,
                                'expanded' => false,
                            ],
                        ],
                        'level' => [
                            'type' => EntityType::class,
                            'options' => [
                                'class' => DicItExperienceLevel::class,
                                'choice_label' => 'value',
                                'label' => 'label.offer.manage.it_experience_level',
                                'placeholder' => 'Kérem válasszon!',
                                'multiple' => false,
                                'expanded' => false,
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->end()
            ->with('Szoftver ismeretek', ['class' => 'col-md-6'])
            ->add('softwareExperiences', CollectionType::class, [
                'label' => 'Szoftver tapasztalatok',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => OfferDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => EntityType::class,
                            'options' => [
                                'class' => DicSoftwareExperience::class,
                                'choice_label' => 'value',
                                'label' => 'label.offer.manage.software_experience',
                                'placeholder' => 'Kérem válasszon!',
                                'multiple' => false,
                                'expanded' => false,
                            ],
                        ],
                        'level' => [
                            'type' => EntityType::class,
                            'options' => [
                                'class' => DicSoftwareExperienceLevel::class,
                                'choice_label' => 'value',
                                'label' => 'label.offer.manage.software_experience_level',
                                'placeholder' => 'Kérem válasszon!',
                                'multiple' => false,
                                'expanded' => false,
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->end()
            ->with('Nyelvek', ['class' => 'col-md-6'])
            ->add('languages', CollectionType::class, [
                'label' => 'Nyelvismeretek',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => OfferDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => EntityType::class,
                            'options' => [
                                'class' => DicLanguage::class,
                                'choice_label' => 'value',
                                'label' => 'label.offer.manage.language',
                                'placeholder' => 'Kérem válasszon!',
                                'multiple' => false,
                                'expanded' => false,
                            ],
                        ],
                        'level' => [
                            'type' => EntityType::class,
                            'options' => [
                                'class' => DicLanguageLevel::class,
                                'choice_label' => 'value',
                                'label' => 'label.offer.manage.language_level',
                                'placeholder' => 'Kérem válasszon!',
                                'multiple' => false,
                                'expanded' => false,
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->end()
            ->with('Elvárások', ['class' => 'col-md-6'])
            ->add('expectations', CollectionType::class, [
                'label' => 'label.offer.manage.expectations',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => DicExpectation::class,
                    'fields' => [
                        'value' => [
                            'type' => TextType::class,
                            'options' => [
                                'label' => 'label.offer.manage.expectation',
                                'attr' => ['placeholder' => 'label.offer.manage.expectation.placeholder'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->end()
            ->end()
            ->tab('Dátumok')
            ->with('Dátumok', ['class' => 'col-md-6'])
            ->add('applicableFromDate', DatePickerType::class, [
                'label' => 'Érvényesség kezdete',
                'format' => 'yyyy-MM-dd',
                'dp_language' => 'hu',
                'dp_default_date' => 'moment',
                'dp_use_current' => false,
                'attr' => [
                    'data-date-format' => 'YYYY-MM-DD'
                ]
            ])
            ->add('expireDate', DatePickerType::class, [
                'label' => 'Érvényesség vége',
                'format' => 'yyyy-MM-dd',
                'dp_language' => 'hu',
                'dp_default_date' => 'moment',
                'dp_use_current' => false,
                'attr' => [
                    'data-date-format' => 'YYYY-MM-DD'
                ]
            ])
            ->add('numberOfEmployee', IntegerType::class, [
                'label' => 'Alkalmazottak száma',
            ])
            ->end()
            ->with('Szolgáltatások', ['class' => 'col-md-6'])
            ->add('offerExaltationUntil', DatePickerType::class, [
                'label' => 'Kiemelés vége',
                'required' => false,
                'format' => 'yyyy-MM-dd',
                'dp_language' => 'hu',
                'dp_default_date' => 'moment',
                'dp_use_current' => false,
                'attr' => [
                    'data-date-format' => 'YYYY-MM-DD'
                ]
            ])
            ->add('advanceFilterUntil', DatePickerType::class, [
                'label' => 'Bővített szűrő',
                'required' => false,
                'format' => 'yyyy-MM-dd',
                'dp_language' => 'hu',
                'dp_default_date' => 'moment',
                'dp_use_current' => false,
                'attr' => [
                    'data-date-format' => 'YYYY-MM-DD'
                ]
            ])
            ->end()
            ->end()
            ->tab('Kiküldés')
            ->with('Kiküldés', ['class' => 'col-md-6'])
            ->add('minimal_package', CheckboxType::class, [
                'label' => 'Minimal csomag',
                'required' => false,
            ])
            ->add('minimal_email', EmailType::class, [
                'label' => 'Minimal értesítendő e-mail cím',
                'required' => false,
            ])
            ->add('minimal_title', null, [
                'label' => 'A hírdetés címe az e-mailben',
                'required' => false,
            ])
            ->add('minimal_url', null, [
                'label' => 'A hírdetés url-je az e-mailben',
                'required' => false,
            ])
            ->add('minimal_without_cv', null, [
                'label' => 'Kizárólag önéletrajzzal rendelkező munkavállalók továbbítása',
                'required' => false,
            ])
            ->end()
            ->end()

//            ->add('companyHelps', AutocompleteType::class, [
//                'descriptor' => 'dic_company_help.descriptor',
//                'label' => 'TODO companyHelps',
//                'multiple' => true,
//            ])
//            ->add('lifeStyles', AutocompleteType::class, [
//                'descriptor' => 'dic_life_style.descriptor',
//                'label' => 'TODO lifestyle',
//                'multiple' => true,
//            ])
//            ->add('marketStatuses', AutocompleteType::class, [
//                'descriptor' => 'dic_market_status.descriptor',
//                'label' => 'TODO marketstatus',
//                'multiple' => true
//            ])

        ;
    }

       protected function configureDatagridFilters(DatagridMapper $datagridMapper)
{
    $datagridMapper
        ->add('firm.name', null, [
            'label' => 'Cég neve',
        ])
        ->add('title', 'doctrine_orm_callback', [
            'callback' => function ($queryBuilder, $alias, $field, $value) {
                if (!$value || !isset($value['value']) || '' == $value['value']) {
                    return false;
                }
                
                $searchTerm = trim($value['value']);
                
                if (strlen($searchTerm) < 3) {
                    return false;
                }
                
                $queryBuilder->leftJoin($alias.'.firm', 'f');
                
                // 1. TELJES SZÖVEG keresés
                $fullTextCondition = $alias . '.title LIKE :fullTerm';
                
                // 2. SZAVAS keresés
                $words = explode(' ', $searchTerm);
                $validWords = [];
                
                foreach ($words as $word) {
                    $word = trim($word);
                    if (strlen($word) > 2 && !in_array(strtolower($word), ['és', 'egy', 'van', 'még', 'azt', 'ami', 'hogy'])) {
                        $validWords[] = $word;
                    }
                }
                
                $validWords = array_slice($validWords, 0, 4);
                
                $wordConditions = [];
                if (!empty($validWords)) {
                    foreach ($validWords as $index => $word) {
                        $paramName = 'w' . $index;
                        $wordConditions[] = '(' . $alias . '.title LIKE :' . $paramName . ' OR f.name LIKE :' . $paramName . ' OR f.nameLong LIKE :' . $paramName . ')';
                        $queryBuilder->setParameter($paramName, '%' . $word . '%');
                    }
                }
                
                // Kombinált WHERE: teljes szöveg VAGY minden szó
                $conditions = [];
                $conditions[] = $fullTextCondition;
                $queryBuilder->setParameter('fullTerm', '%' . $searchTerm . '%');
                
                if (!empty($wordConditions)) {
                    $conditions[] = '(' . implode(' AND ', $wordConditions) . ')';
                }
                
                $queryBuilder->andWhere('(' . implode(' OR ', $conditions) . ')');
                
                // Egyszerű rendezés: csak dátum szerint
                $queryBuilder->addOrderBy($alias . '.createdAt', 'DESC');

                return true;
            },
            'field_type' => 'text',
            'label' => 'Hirdetés címe',
            'show_filter' => true,
        ])
        ->add('status', null, [
            'label' => 'Státusz',
        ], 'choice', [
            'choices' => array_flip(OfferStatusEnum::getReadables()),
        ]);
}
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('createdAt', null, [
                'label' => 'Létrehozás ideje',
                'format' => 'Y-m-d H:i',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('updatedAt', null, [
                'label' => 'Utolsó modósítás ideje',
                'format' => 'Y-m-d H:i',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('title', null, [
                'label' => 'Hírdetés címe',
                'header_style' => 'text-align: center',
                'sortable' => true,
                'template' => 'SonataAdminBundle:CRUD:list__offer_link.html.twig'  // Symfony2/3 template notation
            ])
            ->add('firm.name', null, [
                'label' => 'Cég neve',
                'header_style' => 'text-align: center',
                'sortable' => true,
                'template' => '@SonataAdmin/CRUD/Common/firm_link_list.html.twig',
            ])
            ->add('locations', null, [
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/offer_cities.html.twig',
                'label' => 'Település',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('applicableDateRange', null, [
                'label' => 'Érvényesség',
                'template' => '@SonataAdmin/CRUD/Common/date_range.html.twig',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('status', 'trans', [
                'label' => 'Státusz',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            /*->add('candidatesCount', null, [
                'label' => 'Jelentkezők',
                'header_style' => 'text-align: center',
                'sortable' => true,
                'sort_field_mapping' => ['fieldName' => 'id'],
                'sort_parent_association_mappings' => [],
                'row_align' => 'center',
            ])*/
            ->add('minimal_package', null, [
               'label' => 'Csomag',
               'template' => 'templates/minimal_package.html.twig',
               'header_style' => 'text-align: center',
               'row_align' => 'center',
            ])
            ->add('_custom', null, [
                'label' => 'Jelentkezők lista',
                'template' => '@SonataAdmin/CRUD/OfferList/offer_candidate_list.html.twig',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('_action', null, [
                'label' => 'Opciók',
                'actions' => [
                    'accept' => [
                        'template' => '@SonataAdmin/CRUD/BOOffer/list__accept_offer.html.twig',
                    ],
                    'deny' => [
                        'template' => '@SonataAdmin/CRUD/BOOffer/list__deny_offer.html.twig',
                    ],
                    'edit' => [],
                    'duplicate' => [
                        'template' => '@SonataAdmin/CRUD/BOOffer/list__action_duplicate.html.twig',
                    ],
                    'delete' => [],
                ],
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ]);

    }

    protected function configureBatchActions($actions): array
    {
        $actions = parent::configureBatchActions($actions);
        
        $actions['minimal_package_enable'] = [
            'label' => 'batch_action_minimal_package_enable',
            'ask_confirmation' => false,
            'translation_domain' => 'messages'
        ];
        
        $actions['minimal_package_disable'] = [
            'label' => 'batch_action_minimal_package_disable', 
            'ask_confirmation' => false,
            'translation_domain' => 'messages'
        ];
        
        $actions['activate_offers'] = [
            'label' => 'batch_action_activate_offers',
            'ask_confirmation' => false,
            'methods' => ['POST'],
            'translation_domain' => 'messages'
        ];
        
        $actions['deactivate_offers'] = [
            'label' => 'batch_action_deactivate_offers',
            'ask_confirmation' => false,
            'methods' => ['POST'],
            'translation_domain' => 'messages'
        ];
        
        return $actions;
    }

    /**
     * Statisztikák lekérése a template számára
     */
    public function getOfferStatistics()
    {
        try {
            $em = $this->getConfigurationPool()->getContainer()->get('doctrine.orm.entity_manager');
            $offerRepository = $em->getRepository('CommonCoreBundle:Offer\Offer');
            
            return [
                'active' => $offerRepository->getActiveOffersCount(),
                'inactive' => $offerRepository->getInactiveOffersCount(),
                'total' => $offerRepository->getTotalOffersCount(),
            ];
        } catch (\Exception $e) {
            return [
                'active' => 0,
                'inactive' => 0,
                'total' => 0,
            ];
        }
    }

    /**
     * Generate frontend URL for offer
     * 
     * @param Offer $offer
     * @return string
     */
    private function generateFrontendUrl(Offer $offer)
    {
        $router = $this->getConfigurationPool()->getContainer()->get('router');
        
        // Absolute URL generálás a frontend route-hoz
        return $router->generate('show_offer', [
            'slug' => $offer->getSlug(),
            '_locale' => 'hu' // vagy $offer->getLocale() ha többnyelvű
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

}
