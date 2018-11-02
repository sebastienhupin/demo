<?php

namespace Opentalent\ThemeGallery\Wizard;

use TYPO3\CMS\Backend\View\Wizard\Element\BackendLayoutWizardElement;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Description of ThemeGalleryBackendLayoutWizardElement
 *
 * @author sebastienhupin
 */
class ThemeGalleryBackendLayoutWizardElement extends BackendLayoutWizardElement {
    /**
     * @var array
     */
    protected $rows = [];
    /**
     * @var int
     */
    protected $colCount = 0;
    /**
     * @var int
     */
    protected $rowCount = 0;

    /**
     * @return array
     */
    public function render()
    {
        $lang = $this->getLanguageService();
        $resultArray = $this->initializeResultArray();
        $this->init();
        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldInformationResult, false);
        $fieldWizardResult = $this->renderFieldWizard();
        $fieldWizardHtml = $fieldWizardResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldWizardResult, false);
        $json = json_encode($this->rows, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
        $html = [];
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item">';
        $html[] =   $fieldInformationHtml;
        $html[] =   '<div class="form-control-wrap">';
        $html[] =       '<div class="form-wizards-wrap">';
        $html[] =           '<div class="form-wizards-element">';
        $html[] =               '<input';
        $html[] =                   ' type="hidden"';
        $html[] =                   ' name="' . htmlspecialchars($this->data['parameterArray']['itemFormElName']) . '"';
        $html[] =                   ' value="' . htmlspecialchars($this->data['parameterArray']['itemFormElValue']) . '"';
        $html[] =                   '/>';
        $html[] =               '<table class="grideditor table table-bordered">';
        $html[] =                   '<tr>';
        $html[] =                       '<td class="editor_cell">';
        $html[] =                           '<div';
        $html[] =                               ' id="editor"';
        $html[] =                               ' class="t3js-grideditor"';
        $html[] =                               ' data-data="' . htmlspecialchars($json) . '"';
        $html[] =                               ' data-rowcount="' . (int)$this->rowCount . '"';
        $html[] =                               ' data-colcount="' . (int)$this->colCount . '"';
        $html[] =                               ' data-field="' . htmlspecialchars($this->data['parameterArray']['itemFormElName']) . '"';
        $html[] =                           '>';
        $html[] =                           '</div>';
        $html[] =                       '</td>';
        $html[] =                       '<td>';
        $html[] =                           '<div class="btn-group-vertical">';
        $html[] =                               '<a class="btn btn-default btn-sm t3js-grideditor-addcolumn" href="#"';
        $html[] =                                   ' title="' . htmlspecialchars($lang->getLL('grid_addColumn')) . '">';
        $html[] =                                   '<i class="fa fa-fw fa-arrow-right"></i>';
        $html[] =                               '</a>';
        $html[] =                               '<a class="btn btn-default btn-sm t3js-grideditor-removecolumn" href="#"';
        $html[] =                                   ' title="' . htmlspecialchars($lang->getLL('grid_removeColumn')) . '">';
        $html[] =                                   '<i class="fa fa-fw fa-arrow-left"></i>';
        $html[] =                               '</a>';
        $html[] =                           '</div>';
        $html[] =                       '</td>';
        $html[] =                   '</tr>';
        $html[] =                   '<tr>';
        $html[] =                       '<td colspan="2" align="center">';
        $html[] =                           '<div class="btn-group">';
        $html[] =                               '<a class="btn btn-default btn-sm t3js-grideditor-addrow" href="#"';
        $html[] =                                   ' title="' . htmlspecialchars($lang->getLL('grid_addRow')) . '">';
        $html[] =                                   '<i class="fa fa-fw fa-arrow-down"></i>';
        $html[] =                               '</a>';
        $html[] =                               '<a class="btn btn-default btn-sm t3js-grideditor-removerow" href="#"';
        $html[] =                                   ' title="' . htmlspecialchars($lang->getLL('grid_removeRow')) . '">';
        $html[] =                                   '<i class="fa fa-fw fa-arrow-up"></i>';
        $html[] =                               '</a>';
        $html[] =                           '</div>';
        $html[] =                       '</td>';
        $html[] =                   '</tr>';
        $html[] =                   '<tr>';
        $html[] =                       '<td colspan="2">';
        $html[] =                           '<a href="#" class="btn btn-default btn-sm t3js-grideditor-preview-button"></a>';
        $html[] =                           '<pre class="t3js-grideditor-preview-config grideditor-preview"><code></code></pre>';
        $html[] =                       '</td>';
        $html[] =                   '</tr>';
        $html[] =               '</table>';
        $html[] =           '</div>';
        $html[] =           '<div class="form-wizards-items-bottom">';
        $html[] =               $fieldWizardHtml;
        $html[] =           '</div>';
        $html[] =       '</div>';
        $html[] =   '</div>';
        $html[] = '</div>';
        $ctypes = array();
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $item) {
            $ctype = array();
            $ctype['key'] = $item[1];
            if (substr($ctype['key'], 0, 2) !== '--') {
                $ctype['label'] = $lang->sL($item[0], true);
                if (strpos($item[2], 'EXT:') === 0) {
                    $ctype['icon'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($item[2]);
                } elseif (strpos($item[2], '/typo3') === 0) {
                    $ctype['icon'] = '../../../' . $item[2];
                } else {
                    $ctype['icon'] = '../../../' . '../typo3/sysext/core/Resources/Public/Icons/T3Icons/content/' . $item[2];
                }
                // Check if file ending exists, therefore compare pos of last slash to pos of last dot
                if (!empty($ctype['icon']) && strrpos($ctype['icon'], '/') > strrpos($ctype['icon'], '.')) {
                  $ctype['icon'] .= '.svg';
                }
                $ctypes[] = $ctype;
            }
        }
        $html[] = '<script type="text/javascript">/*<![CDATA[*/ TYPO3.settings.availableCTypes = '. json_encode($ctypes) .'; /*]]>*/</script>';
        $html = implode(LF, $html);
        $resultArray['html'] = $html;
        $resultArray['additionalInlineLanguageLabelFiles'][] = 'EXT:theme_gallery/Resources/Private/Language/locallang_wizard.xlf';
        $resultArray['additionalInlineLanguageLabelFiles'][] = 'EXT:lang/Resources/Private/Language/locallang_wizards.xlf';
        $resultArray['additionalInlineLanguageLabelFiles'][] = 'EXT:backend/Resources/Private/Language/locallang.xlf';
        $resultArray['requireJsModules'][] = 'TYPO3/CMS/ThemeGallery/GridEditor';

        return $resultArray;
    }

    /**
     * Initialize wizard
     */
    protected function init()
    {
        if (empty($this->data['databaseRow']['config'])) {
            $rows = [[['colspan' => 1, 'rowspan' => 1, 'spanned' => 0, 'name' => '0x0', 'maxElement' => 1000]]];
            $colCount = 1;
            $rowCount = 1;
        } else {
            // load TS parser
            $parser = GeneralUtility::makeInstance(TypoScriptParser::class);
            $parser->parse($this->data['databaseRow']['config']);
            $data = $parser->setup['backend_layout.'];
            $rows = [];
            $colCount = $data['colCount'];
            $rowCount = $data['rowCount'];            
            $dataRows = $data['rows.'];
            $spannedMatrix = [];
            for ($i = 1; $i <= $rowCount; $i++) {
                $cells = [];
                $row = array_shift($dataRows);
                $columns = $row['columns.'];
                for ($j = 1; $j <= $colCount; $j++) {
                    $cellData = [];
                    if (!$spannedMatrix[$i][$j]) {
                        if (is_array($columns) && !empty($columns)) {
                            $column = array_shift($columns);
                            if (isset($column['colspan'])) {
                                $cellData['colspan'] = (int)$column['colspan'];
                                $columnColSpan = (int)$column['colspan'];
                                if (isset($column['rowspan'])) {
                                    $columnRowSpan = (int)$column['rowspan'];
                                    for ($spanRow = 0; $spanRow < $columnRowSpan; $spanRow++) {
                                        for ($spanColumn = 0; $spanColumn < $columnColSpan; $spanColumn++) {
                                            $spannedMatrix[$i + $spanRow][$j + $spanColumn] = 1;
                                        }
                                    }
                                } else {
                                    for ($spanColumn = 0; $spanColumn < $columnColSpan; $spanColumn++) {
                                        $spannedMatrix[$i][$j + $spanColumn] = 1;
                                    }
                                }
                            } else {
                                $cellData['colspan'] = 1;
                                if (isset($column['rowspan'])) {
                                    $columnRowSpan = (int)$column['rowspan'];
                                    for ($spanRow = 0; $spanRow < $columnRowSpan; $spanRow++) {
                                        $spannedMatrix[$i + $spanRow][$j] = 1;
                                    }
                                }
                            }
                            if (isset($column['rowspan'])) {
                                $cellData['rowspan'] = (int)$column['rowspan'];
                            } else {
                                $cellData['rowspan'] = 1;
                            }
                            if (isset($column['name'])) {
                                $cellData['name'] = $column['name'];
                            }
                            if (isset($column['colPos'])) {
                                $cellData['column'] = (int)$column['colPos'];
                            }
                            if (isset($column['allowed'])) {
                                $cellData['allowed'] = $column['allowed'];
                            }
                            if (isset($column['allowedGridTypes'])) {
                                $cellData['allowedGridTypes'] = $column['allowedGridTypes'];
                            }
                            if (isset($column['maxElement'])) {
                                $cellData['maxElement'] = $column['maxElement'];
                            }                            
                        }
                    } else {
                        $cellData = ['colspan' => 1, 'rowspan' => 1, 'spanned' => 1];
                    }
                    $cells[] = $cellData;
                }
                $rows[] = $cells;
                if (!empty($spannedMatrix[$i]) && is_array($spannedMatrix[$i])) {
                    ksort($spannedMatrix[$i]);
                }
            }
        }
        $this->rows = $rows;
        $this->colCount = (int)$colCount;
        $this->rowCount = (int)$rowCount;
    }
    
}
