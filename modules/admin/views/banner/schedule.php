<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\models\User;
use app\helpers\Constants;

$this->title = Yii::t('admin','Manage schedule');
$this->params['breadcrumbs'][] = ['label' => Yii::t('admin','Banner place list'), 'url' => Url::to(['/admin/banner/places'])];
$this->params['breadcrumbs'][] = $this->title;

/* @var $controller \app\modules\admin\controllers\BannerController */
/* @var $this \yii\web\View */
/* @var $model \app\models\BannerPlace */
/* @var $banners \app\models\Banner[] */
/* @var $calendarConfig string */

$controller = $this->context;
$lng = Yii::$app->language;

$this->registerCssFile("@web/js/fullcalendar/fullcalendar.min.css");
$this->registerJsFile("@web/js/jQueryUI/jquery-ui.js",['position' => \yii\web\View::POS_BEGIN]);
$this->registerJsFile("@web/js/moment/moment.js",['position' => \yii\web\View::POS_BEGIN]);
$this->registerJsFile("@web/js/fullcalendar/fullcalendar.min.js",['position' => \yii\web\View::POS_BEGIN]);
$this->registerJsFile("@web/js/fullcalendar/lang/{$lng}.js",['position' => \yii\web\View::POS_BEGIN]);
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><?= Yii::t('admin','Settings'); ?></h3></div>

            <div class="box-body">

                <div class="row">
                    <div class="col-md-3">
                        <div class="box box-solid">
                            <div class="box-header with-border">
                                <h4 class="box-title"><?= Yii::t('admin','Banners') ?></h4>
                            </div>
                            <div class="box-body">
                                <?php if(!empty($banners)): ?>
                                    <div id="external-events">
                                        <?php foreach($banners as $banner): ?>
                                            <div data-id="<?php echo $banner->id; ?>" class="external-event bg-light-blue"><?php echo $banner->name; ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p><?= Yii::t('admin','No banners found. You can add new banner in banner list'); ?></p>
                                <?php endif; ?>
                            </div><!-- /.box-body -->
                        </div><!-- /. box -->
                    </div><!-- /.col -->
                    <div class="col-md-9">
                        <div class="box box-primary">
                            <div class="box-body no-padding">
                                <!-- THE CALENDAR -->
                                <div id="calendar"></div>
                            </div><!-- /.box-body -->
                        </div><!-- /. box -->
                    </div><!-- /.col -->
                </div>

            </div>

            <div class="box-footer">
                <a class="btn btn-primary" href="<?php echo Url::to(['/admin/banner/places']); ?>"><?= Yii::t('admin','Back'); ?></a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {

        //Initialise event items (banners)
        function initEvents(elements) {
            elements.each(function () {

                var eventObject = {
                    title: $.trim($(this).text()),
                    banner_id: $(this).data('id')
                };

                $(this).data('eventObject', eventObject);

                $(this).draggable({
                    zIndex: 1070,
                    revert: true,
                    revertDuration: 0
                });
            });
        }

        //Initialise on load
        initEvents($('#external-events div.external-event'));

        //Get calendar DIV
        var calendarElement = $("#calendar");

        
        //Init calendar
        calendarElement.fullCalendar({
            lang: '<?= $lng ?>',
            allDaySlot: false,
            defaultView : 'agendaWeek',
            scrollTime : '00:00:00',
            slotDuration : '00:30:00',
            defaultDate : '<?= date('Y-m-d H:i:s',time()); ?>',

            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'agendaWeek,agendaDay'
            },

            buttonText: {
                today: '<?= Yii::t('admin','today'); ?>',
                month: '<?= Yii::t('admin','month'); ?>',
                week: '<?= Yii::t('admin','week'); ?>',
                day: '<?= Yii::t('admin','day'); ?>'
            },

            events: <?= $calendarConfig ?>,

            editable: true,
            droppable: true,

            //When dropping events on calendar
            drop: function (date, allDay) {
                var originalEventObject = $(this).data('eventObject');
                var copiedEventObject = $.extend({}, originalEventObject);

                copiedEventObject.start = date;
                copiedEventObject.backgroundColor = '#3c8dbc';
                copiedEventObject.borderColor = '#3c8dbc';

                $.ajax({
                    url:'<?= Url::to(['/admin/banner/add-time','id' => $model->id]); ?>',
                    method:"POST",
                    data:{
                        banner_id: copiedEventObject.banner_id,
                        start_date: date.format('YYYY-MM-DD HH:mm:ss')
                    }
                }).done(function(response) {
                    if(response !== 'FAILED'){

                        copiedEventObject.start = response.start_date;
                        copiedEventObject.end = response.end_date;
                        copiedEventObject.item_id = response.id;

                        calendarElement.fullCalendar('renderEvent', copiedEventObject, true);
                    }
                });
            },

            //When resizing events
            eventResize:function(event, jsEvent, ui, view){
                $.ajax({
                    url: "<?php echo Url::to(['/admin/banner/edit-time']); ?>",
                    method:"POST",
                    data: {
                        id: event.item_id,
                        start_date: event.start.format('YYYY-MM-DD HH:mm:ss'),
                        end_date: event.end.format('YYYY-MM-DD HH:mm:ss')
                    }
                }).done(function(data){
                    //TODO: handle success
                }).fail(function(){
                    //TODO: handle failure
                });
            },

            //When moving inside the calendar
            eventDrop:function(event, delta, revertFunc, jsEvent, ui, view ){
                $.ajax({
                    url: "<?php echo Url::to(['/admin/banner/edit-time']); ?>",
                    method:"POST",
                    data: {
                        id: event.item_id,
                        start_date: event.start.format('YYYY-MM-DD HH:mm:ss'),
                        end_date: event.end.format('YYYY-MM-DD HH:mm:ss')
                    }
                }).done(function(data){
                    //TODO: handle success
                }).fail(function(){
                    //TODO: handle failure
                });
            },

            //Add delete option (just icons)
            eventRender: function(event, eventElement) {
                eventElement.find("div.fc-content").prepend("<i data-item-id='"+event.item_id+"' data-event='"+event._id+"' class='fa fa-close pull-right event-del'></i>");
            }
        });

        //When clicked on delete icon
        calendarElement.on('click','.event-del',function(){
            var button = $(this);
            if (confirm("<?= Yii::t('yii','Are you sure you want to delete this item?'); ?>")){
                $.ajax({
                    url: "<?php echo Url::to(['/admin/banner/delete-time']); ?>",
                    data: {
                        id: button.data('item-id')
                    }
                }).done(function(response) {
                    if(response !== 'FAILED'){
                        calendarElement.fullCalendar('removeEvents',button.data('event'));
                    }
                });
            }
            return false;
        });
    });
</script>
