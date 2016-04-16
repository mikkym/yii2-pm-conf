<?php

use yii\helpers\Html;
use yii\bootstrap\Html as BootHtml;

use yii\widgets\ActiveForm;
use common\models\Member;

/* @var $this yii\web\View */
/* @var $model common\models\Member */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="member-form registration">

    <?php if (count($model->errors) > 0): ?>
        <pre class="text-danger">
        <?php
        Yii::$app->session->setFlash('error', Yii::t('app', 'Something went wrong :('));
        print_r($model->errors);
        ?>
    </pre>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'register-member',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-4\">{input}</div>\n<div class=\"col-lg-4\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-4 control-label'],
        ],
    ]); ?>

    <?= $form->field($model, 'country')->dropDownList(Member::getCountryVariants()) ?>

    <h3 class="text-center text-info middle-header">
        <?= Yii::t('app.member', 'Information about the participant') ?>
    </h3>

    <?= $form->field($model, 'name') ?>
    <?= $form->field($model, 'position') ?>
    <?= $form->field($model, 'phone') ?>
    <?= $form->field($model, 'email') ?>
    <?= $form->field($model, 'interest') ?>
    <?= $form->field($model, 'scienceDegree')->dropDownList(Member::getScienceDegreeVariants()) ?>
    <?= $form->field($model, 'scienceTitle')->dropDownList(Member::getScienceTitleVariants()) ?>

    <h3 class="text-center text-info middle-header">
        <?= Yii::t('app.member', 'Organisation information') ?>
    </h3>

    <?= $form->field($model, 'organisationTitle') ?>
    <?= $form->field($model, 'organisationDepartment') ?>
    <?= $form->field($model, 'organisationAddress') ?>
    <?= $form->field($model, 'organisationActivity')->dropDownList(Member::getOrganisationActivityVariants()) ?>
    <?= $form->field($model, 'organisationUrl') ?>



    <h3 class="text-center text-info middle-header">
        <?= Yii::t('app.member', 'Form of participation') ?>
    </h3>

    <?= $form->field($model, 'nameEng') ?>
    <?= Html::activeRadioList($model, 'participationType', Member::getParticipationTypeVariants(), [
        'item' => function ($index, $label, $name, $checked, $value) {
            if ($index == 0) {
                return '<div class="col-lg-3 col-lg-offset-3"><label class="radio-inline">' . Html::radio($name, $checked, ['value' => $value]) . $label . '</label></div>';
            }
            return '<div class="col-lg-6"><label class="radio-inline">' . Html::radio($name, $checked, ['value' => $value]) . $label . '</label></div>';
        }
    ]); ?>

    <div class="hidden info-for-speaker">
        <?= $form->field($model, 'topicTitle', [
            'inputOptions' => [
                'placeholder' => $model->getAttributeLabel('topicTitle'),
                'class' => 'form-control'
            ],
            'template' => "\n<div class=\"col-lg-6 col-lg-offset-3\">{input}</div>\n<div class=\"col-lg-3 \">{error}</div>"
        ]) ?>
        <div class="row">
            <div class="col-lg-4 col-lg-offset-2">
                <?= BootHtml::activeDropDownList(
                    $model,
                    'topicLanguage',
                    Member::getTopicLanguageVariants(),
                    ['class' => 'form-control']
                ) ?>
            </div>
            <div class="col-lg-4">
                <?= BootHtml::activeDropDownList(
                    $model,
                    'topicSection',
                    Member::getTopicSectionVariants(),
                    ['class' => 'form-control']
                ) ?>
            </div>
        </div>
    </div>


    <h3 class="text-center text-info middle-header">
        <?= Yii::t('app.member', 'Payment') ?>
    </h3>

    <table class="table table-bordered summary-info text-center" >
        <tr class="success">
            <th><?= Yii::t('app.member', 'Full name') ?></th>
            <th><?= Yii::t('app.member', 'Registration fee') ?></th>
            <th class=""><?= Yii::t('app.member', 'Conference papers') ?></th>
            <th><?= Yii::t('app.member', 'Total to pay') ?></th>
        </tr>
        <tr>
            <td id="table-full-name"></td>
            <td id="table-registration-fee"></td>
            <td class=""><?=
                BootHtml::activeDropDownList(
                    $model,
                    'papersCount',
                    [
                        'no' => 'No papers',
                        1 => 1,
                        2 => 2,
                        3 => 3,
                        4 => 4,
                        5 => 5,
                        6 => 6,
                        7 => 7,
                        8 => 8,
                        9 => 9,
                        10 => 10,
                    ],
                    ['class' => 'form-control ']
                )
                ?>
                <span id="price-for-papers"></span>
            </td>
            <td class="total-sum"></td>
        </tr>
    </table>
    <div class="row">
        <h2 class="pull-right super-total">
            <?= Yii::t('app.member', 'Total sum'); ?>
            <span class="total-sum text-primary"></span>
        </h2>
    </div>


    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Create'), ['class' => 'btn btn-primary pull-right']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$bindFullName = <<< JS

var selectedCountry;
var registrationFee;
var priceForPapers;

$('#member-name').on('change', function(){
    setTableFullName();
});
$('#member-country').on('change', function(){
    setTableRegistrationFee();
    setPapersPrice();
    setTotalPrice();
});
$('#member-paperscount').on('change', function(){
    setPapersPrice();
    setTotalPrice();
});

$('input[type=radio]', '#register-member').on('change', function(){
    if($(this).val() == 'speaker'){
        $('.info-for-speaker').removeClass('hidden');
    }else{
        $('.info-for-speaker').addClass('hidden');
    }
});


function setTableFullName(){
    var nameField = $('#member-name').val();
    $('#table-full-name').html(nameField || 'No name');
}
setTableFullName();

function setTableRegistrationFee(){
    selectedCountry = $('#member-country').val();
    var tableField;

    if(selectedCountry == 'ua'){
        registrationFee = 350;
        tableField = registrationFee + ' грн';
    }else{
        registrationFee = 100;
        tableField = '$' + registrationFee;
    }
    $('#table-registration-fee').html(tableField);
}
setTableRegistrationFee();

function setPapersPrice(){
    var papersCount = +$('#member-paperscount').val();
    var priceForPapersLabel;

    if(selectedCountry == 'ua'){
        priceForPapers = papersCount * 150;
        priceForPapersLabel = priceForPapers + ' грн';
    }else{
        priceForPapers = papersCount * 50;
        priceForPapersLabel = '$' + priceForPapers;
    }
    if(papersCount > 0){
        $('#price-for-papers').html(priceForPapersLabel);
    }
}

function setTotalPrice(){
    var totalSum = 0;

    if(priceForPapers){
        totalSum = +registrationFee + +priceForPapers;
    }else{
        totalSum = +registrationFee ;
    }

    if(selectedCountry == 'ua'){
        $('.total-sum').html(totalSum + ' грн');
    }else{
        $('.total-sum').html('$' + totalSum );
    }
}
setTotalPrice();

JS;


$this->registerJs($bindFullName);

?>
