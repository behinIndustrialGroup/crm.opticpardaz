<?php 

use App\CustomClasses\UserInfo;
use App\CustomClasses\Access;
use App\CustomClasses\NumberOf;
use App\Models\IssuesCatagoryModel;
use App\Models\VideosCatagoriesModel;

$user = Auth::user();
$issues_catagories = IssuescatagoryModel::get();
$videosCatagories = VideosCatagoriesModel::get();
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <img src="{{ url('public/dashboard/dist/img/avatar5.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">پنل مدیریت</span>
    </a>

    <div class="sidebar" style="direction: ltr">
        <div style="direction: rtl">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ url('public/dashboard/dist/img/avatar5.png') }}"
                        class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ auth()->user()->name ?? ''}}</a>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">
                    @foreach (config('sidebar.menu') as $menu)
                        @if ( auth()->user()->access('منو >>' .$menu['fa_name']) )
                          <li class="nav-item has-treeview">
                            <a href="#" class="nav-link active">
                                <i class="nav-icon fa fa-dashboard"></i>
                                <p>
                                    {{ $menu['fa_name'] }}
                                    <i class="right fa fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @foreach ($menu['submenu'] as $submenu)
                                  @if ( auth()->user()->access('منو >>' .$menu['fa_name'] . '>>' . $submenu['fa_name'] ) )
                                    <li class="nav-item">
                                        <a
                                          @isset($submenu['target']) target="{{ $submenu['target'] }}" @endisset
                                         href="@if(Route::has($submenu['route-name'])) 
                                                    {{ route($submenu['route-name']) }} 
                                                @elseif(isset($submenu['static-url']))
                                                    {{ $submenu['static-url'] }}
                                                @else
                                                    {{ url($submenu['route-url']) }} 
                                                @endif" class="nav-link active">
                                            <i class="fa fa-circle-o nav-icon"></i>
                                            <p>{{ $submenu['fa_name'] }}</p>
                                        </a>
                                    </li>
                                  @endif
                                @endforeach
                            </ul>
                          </li>
                        @endif
                        
                    @endforeach
                        @if(Access::checkView('Issues_issue_show'))
                            <li class="nav-item has-treeview">
                              <a href="#" class="nav-link active">
                                <i class="fa fa-pie-chart"></i> <span>تیکت</span>
                                <span class="pull-left-container">
                                  <i class="fa fa-angle-right pull-left"></i>
                                </span>
                              </a>
                              <ul class="nav nav-treeview" style="display: none;">
                                  @foreach($issues_catagories as $catagory)
                                    <li class="nav-item" style="font-size: 10px"><a class="nav-link active" href="<?php echo Url("admin/issues/show/$catagory->name") ?>"><i class="label pull-left bg-red">{{NumberOf::Ticket($catagory->name)}}</i><i class="fa fa-minus"></i>{{$catagory->fa_name}}</a></li>
                                  @endforeach
                                    <hr>
                                    <li class="nav-item"><a class="nav-link active" href="{{ Url('admin/issues/show/irngvagancy') }}"><i class="label pull-left bg-red">{{NumberOf::Ticket('irngvagancy')}}</i><i class="fa fa-minus"></i>مراکز irngv</a></li>
                                    <hr>
                                    <li class="nav-item"> <a class="nav-link active" href="{{ Url('admin/issues/catagories') }}"><i class="fa fa-minus"></i>دسته بندی ها</a></li>
                                    <li class="nav-item"><a class="nav-link active" href="{{ Url('admin/issues/createIssue') }}"><i class="fa fa-minus"></i>ایجاد تیکت</a></li>
                              </ul>
                            </li>
                        @endif
                        <!--
                        <li class="treeview">
                          <a href="#">
                            <i class="fa fa-envelope"></i> <span>الگوهای ارسال پیامک</span>
                            <span class="pull-left-container">
                              <i class="fa fa-angle-right pull-left"></i>
                            </span>
                          </a>
                          <ul class="treeview-menu" style="display: none;">
                                <li><a href="{{ Url('admin/smstemplate') }}"><i class="fa fa-minus"></i>مالیات</a></li>
                          </ul>
                        </li>
                        <li class="treeview">
                          <a href="#">
                            <i class="fa fa-envelope"></i> <span>فرم ها</span>
                            <span class="pull-left-container">
                              <i class="fa fa-angle-right pull-left"></i>
                            </span>
                          </a>
                          <ul class="treeview-menu" style="display: none;">
                                <li><a href="{{ Url('admin/forms/parvane') }}"><i class="fa fa-minus"></i>صدور پروانه</a></li>
                          </ul>
                        </li>
                        -->
                        {{-- @if(Access::checkView('user_show_all'))
                            <li class="treeview">
                              <a href="#">
                                <i class="fa fa-envelope"></i> <span>کاربران</span>
                                <span class="pull-left-container">
                                  <i class="fa fa-angle-right pull-left"></i>
                                </span>
                              </a>
                              <ul class="treeview-menu" style="display: none;">
                                    <li><a href="{{ Url('admin/user/all') }}"><i class="fa fa-minus"></i>همه</a></li>
                              </ul>
                            </li>
                        @endif
                        @if(Access::checkView('report'))
                            <li class="treeview">
                              <a href="#">
                                <i class="fa fa-envelope"></i> <span>گزارش</span>
                                <span class="pull-left-container">
                                  <i class="fa fa-angle-right pull-left"></i>
                                </span>
                              </a>
                              <ul class="treeview-menu" style="display: none;">
                                    @if(Access::checkView('report_issue'))
                                    <li><a href="{{ Url('admin/report/ticket') }}"><i class="fa fa-minus"></i>تیکت</a></li>
                                    @endif
                                    @if(Access::checkView('report_call'))
                                    <li class="treeview">
                                      <a href="">
                                        <i class="fa fa-minus"></i> <span>تماس</span>
                                        <span class="pull-left-container">
                                          <i class="fa fa-angle-right pull-left"></i>
                                        </span>
                                      </a>
                                      <ul class="treeview-menu" style="display: none;">
                                        <li><a href="{{ Url('admin/report/call') }}"><i class="fa fa-minus"></i>ایجاد</a></li>
                                        <li><a href="{{ Url('admin/report/call/show') }}"><i class="fa fa-minus"></i>مشاهده</a></li>
                                      </ul>
                                    </li>
                                    @endif
                                    @if(Access::checkView('report_license'))
                                    <li><a href="{{ Url('admin/report/license') }}"><i class="fa fa-minus"></i>پروانه کسب</a></li>
                                    @endif
                                    @if(Access::checkView('irngv_poll_report'))
                                      <li><a href="{{ route('report.irngv.poll') }}"><i class="fa fa-minus"></i>نظرسنجی irngv</a></li>
                                    @endif
                              </ul>
                            </li>
                        @endif
                        @if(Access::checkView('Videos_showListpublic'))
                            <li class="treeview">
                              <a href="#">
                                <i class="fa fa-pie-chart"></i> <span>ویدیوهای آموزشی</span>
                                <span class="pull-left-container">
                                  <i class="fa fa-angle-right pull-left"></i>
                                </span>
                              </a>
                              <ul class="treeview-menu" style="display: none;">
                                  @foreach( $videosCatagories as $c )
                                    <li><a href="<?php echo  Url("admin/videos/show/$c->name") ?>"><i class="fa fa-minus"></i>{{ $c->fa_name }}</a></li>
                                  @endforeach
                                    <li><a href="{{ Url('admin/videos/add') }}"><i class="fa fa-minus"></i>افزودن ویدیو</a></li>
                                    <li><a href="{{ Url('admin/videos/addCatagory') }}"><i class="fa fa-minus"></i>افزودن دسته بندی</a></li>
                              </ul>
                            </li>
                        @endif
                        @if(Access::checkView('AsignInsRequest_form'))
                            <li class="treeview">
                              <a href="#">
                                <i class="fa fa-envelope"></i> <span>تخصیص بازرس</span>
                                <span class="pull-left-container">
                                  <i class="fa fa-angle-right pull-left"></i>
                                </span>
                              </a>
                              <ul class="treeview-menu" style="display: none;">
                                    <li><a href="{{ Url('admin/ins/asign/ins/form') }}"><i class="fa fa-minus"></i>درخواست جدید</a></li>
                                    <li><a href="{{ Url('admin/ins/show/all') }}"><i class="fa fa-minus"></i>لیست درخواست ها</a></li>
                              </ul>
                            </li>
                        @endif
                        @if(Access::checkView('Request_form'))
                            <li class="treeview">
                              <a href="#">
                                <i class="fa fa-envelope"></i> <span>درخواست ها</span>
                                <span class="pull-left-container">
                                  <i class="fa fa-angle-right pull-left"></i>
                                </span>
                              </a>
                              <ul class="treeview-menu" style="display: none;">
                                    <li><a href="{{ Url('admin/request/asign/ins/show/all') }}"><i class="fa fa-minus"></i>تخصیص بازرس</a></li>
                              </ul>
                            </li>
                        @endif
                        @if(Access::checkView('robot'))
                            <li class="treeview">
                              <a href="#">
                                <i class="fa fa-envelope"></i> <span>ربات پاسخگویی</span>
                                <span class="pull-left-container">
                                  <i class="fa fa-angle-right pull-left"></i>
                                </span>
                              </a>
                              <ul class="treeview-menu" style="display: none;">
                                    <li><a href="{{ Url('admin/robot/add') }}"><i class="fa fa-minus"></i>افزودن دسته جدید</a></li>
                                    <li><a href="{{ Url('admin/robot/edit') }}"><i class="fa fa-minus"></i>اصلاح پاسخ ها</a></li>
                              </ul>
                            </li>
                        @endif
                        @if(Access::checkView('irngv'))
                            <li class="treeview">
                              <a href="#">
                                <i class="fa fa-envelope"></i> <span>اطلاعات دریافتی از irngv</span>
                                <span class="pull-left-container">
                                  <i class="fa fa-angle-right pull-left"></i>
                                </span>
                              </a>
                              <ul class="treeview-menu" style="display: none;">
                                  @if(Access::checkView('irngv_recive_car_info'))
                                    <li><a href="{{ route('admin.irngv.show.list') }}"><i class="fa fa-minus"></i>اطلاعات دریافتی</a></li>
                                  @endif
                                  @if(Access::checkView('irngv_poll_info'))
                                    <li><a href="{{ route('admin.irngv.show.answers') }}"><i class="fa fa-minus"></i>اطلاعات نظرسنجی </a></li>
                                  @endif
                              </ul>
                            </li>
                        @endif
                        @if(Access::checkView('Disable_App'))
                          <li>
                            <a href="{{ Url('admin/disable') }}"><i class="fa fa-book"></i> <span>غیرفعال کردن نرم افزار</span></a>
                          </li>
                        @endif
                        @if(Access::checkView('show_options'))
                          <li>
                            <a href="{{ Url('admin/options') }}"><i class="fa fa-book"></i> <span>گزینه های بیشتر</span></a>
                          </li>
                        @endif
                        @if(Access::checkView('hamayesh'))
                            <li class="treeview">
                              <a href="#">
                                <i class="fa fa-pie-chart"></i> <span>همایش</span>
                                <span class="pull-left-container">
                                  <i class="fa fa-angle-right pull-left"></i>
                                </span>
                              </a>
                              <ul class="treeview-menu" style="display: none;">
                                    <li><a href="{{ route('add-class-form') }}"><i class="fa fa-minus"></i>افزودن دوره آموزشی</a></li>
                                    <li><a href="{{ route('hamayesh-list') }}"><i class="fa fa-minus"></i>لیست ثبت نامی ها</a></li>
            
                              </ul>
                            </li>
                        @endif --}}
                </ul>
            </nav>
        </div>
    </div>
</aside>


