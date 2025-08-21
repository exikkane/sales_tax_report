{capture name="sales_report_details"}
    <form name="sales_report_details_form" action="{""|fn_url}" method="post" enctype="multipart/form-data" class="form-horizontal">
        <div id="sales_report_details">
            <div class="control-group">
                {include file="common/period_selector.tpl" period=$search.period form_name="sales_report_details_form"}
            </div>
            <label class="control-label">{__("format")}:</label>
            <div class="controls">
                <label class="radio">
                    <input type="radio" checked="checked" id="elm_report_format"/>
                    <div>
                        <input type="radio" id="sales_format_csv" name="format" value="C" checked />
                        <label for="C">CSV</label>
                    </div>
                    <div>
                        <input type="radio" id="sales_format_xlsx" name="format" value="X" />
                        <label for="X">XLSX</label>
                    </div>
                </label>
            </div>
        </div>

        <div class="buttons-container">
            {include file="buttons/save_cancel.tpl" but_name="dispatch[companies.generate_report]" cancel_action="close"}
        </div>
    </form>
{/capture}

{if $auth.user_type == 'V'}
    <p style="margin-left: 10px">
        {include file="common/popupbox.tpl"
            id="sales_report_details"
            text=__("sales_report_btn")
            content=$smarty.capture.sales_report_details
            title=__("sales_report_btn")
            link_text=__("sales_report_btn")
            act="general"
            link_class="btn-primary"
        }
    </p>
{/if}
