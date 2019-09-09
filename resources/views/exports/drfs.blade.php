<!DOCTYPE html>
<html>
   <head>
      <style type="text/css">
         body {
            font-weight: 1px;
         }
         table {
            border-collapse: collapse;
            margin-bottom: .5em;
         }
         table, th, td {
            border: 1px solid black;
            border-style: solid;
            font-size: 8px;
         }
         h5 {
            margin-top: 6px;
            margin-bottom: 6px;
         }
         p {
            margin-top: 2px;
            font-size: 8px;
         }
         * {
            font-size: 8px;
         }
      </style>
   </head>
   <body>
   @foreach($allocation->details as $detail)
      @php
         $testType = 'consumables';
         if ($detail->testtype == 1) {
            $testType = 'EID';
         } else if ($detail->testtype == 2) {
            $testType = 'VL';
         }
      @endphp
      <table class="table" border="0" style="width: 100%; border:none;">
         <tr>
            <td colspan="7" align="center" style="border: none;">
            <img src="http://lab-2.test.nascop.org/img/naslogo.jpg" alt="NASCOP">
            </td>
         </tr>
         <tr>
            <td colspan="7" align="center" style="border: none;">
               <strong>
               <h5 style="text-transform: uppercase;">{{ $detail->machine->machine ?? '' }} {{ $testType }} DISTRIBUTION REQUEST FORM</h5>
               </strong>
            </td>
         </tr>
      </table>
      <br />
      <table class="table" border="0" style="width: 100%; border:none;">
         <tr>
            <td>
               <table class="table" border="1" style="width: 100%; border: 1px solid;">
                  <tr>
                     <td colspan="2">{{ $master_data['to']['name'] }}</td>
                  </tr>
                  <tr>
                     <td colspan="2">{{ $master_data['to']['address'] }}</td>
                  </tr>
                  <tr>
                     <td>{{ __('Contact Name 1') }}</td>
                     <td>{{ $master_data['to']['contact_name_1'] }}</td>
                  </tr>
                  <tr>
                     <td>{{ __('Tel Number:') }}</td>
                     <td>{{ $master_data['to']['telephone_1'] }}</td>
                  </tr>
                  <tr>
                     <td>{{ __('Contact Name 2:') }}</td>
                     <td>{{ $master_data['to']['contact_name_2'] }}</td>
                  </tr>
                  <tr>
                     <td>{{ __('Tel Number') }}</td>
                     <td>{{ $master_data['to']['telephone_2'] }}</td>
                  </tr>
               </table>
            </td>
            <td>
               <table class="table" border="1" style="width: 100%; border: 1px solid;">
                  <tr>
                     <td colspan="2">{{ __('FROM') }}</td>
                  </tr>
                  <tr>
                     <td colspan="2">{{ $master_data['to']['address'] }}</td>
                  </tr>
                  <tr>
                     <td>{{ __('Contact Name 1') }}</td>
                     <td>{{ $master_data['from']['contact_name_1'] }}</td>
                  </tr>
                  <tr>
                     <td>{{ __('Tel Number:') }}</td>
                     <td>{{ $master_data['from']['telephone_1'] }}</td>
                  </tr>
                  <tr>
                     <td>{{ __('Contact Name 2:') }}</td>
                     <td>{{ $master_data['from']['contact_name_2'] }}</td>
                  </tr>
                  <tr>
                     <td>{{ __('Tel Number') }}</td>
                     <td>{{ $master_data['from']['telephone_2'] }}</td>
                  </tr>
               </table>
            </td>
         </tr>
      </table>          
      <br />
      <table class="table" border="0" style="width: 100%; border:none;">
         <tr>
            <td>
               <table class="table" border="1" style="width: 100%; border: 1px solid;">
                  <tr>
                     <td>{{ __('Order Date') }}</td>
                     <td>{{ date('d-M-Y', strtotime($allocation->orderdate)) }}</td>
                  </tr>
                  <tr>
                     <td>{{ __('Order No.') }}</td>
                     <td>{{ $allocation->order_num }}</td>
                  </tr>
               </table>
            </td>
            <td>
               <table class="table" border="1" style="width: 100%; border: 1px solid;">
                  <tr>
                     <td colspan="4">{{ __('Delivery Date') }}</td>
                  </tr>
                  <tr>
                     <td>{{ __('From') }}</td>
                     <td>{{ date('d-M-Y', strtotime($allocation->orderdate)) }}</td>
                     <td>{{ __('To') }}</td>
                     <td>{{ date('d-M-Y', strtotime($allocation->orderdate)) }}</td>
                  </tr>
               </table>
            </td>
         </tr>
      </table>
      <br>
      <table class="table" border="1" style="width:100%; border: 1px solid;">
         <thead>
            <tr>
               <td>No.</td>
               <td>Description of Goods</td>
               <td>Unit</td>
               <td>Product No</td>
               <td>Quantity</td>
               <td>TOTALS</td>    
            </tr>
         </thead>
         <tbody>
         @foreach($detail->breakdowns as $key => $allocation_breakdown)
            <tr>
               <td>{{ $key + 1 }}</td>
               <td>{{ $allocation_breakdown->breakdown->name }}</td>
               <td>{{ $allocation_breakdown->breakdown->unit ?? '' }}</td>
               <td>{{ '' }}</td>
               <td>{{ $allocation_breakdown->allocated }}</td>
               <td>{{ $allocation_breakdown->allocated }}</td>
            </tr>
         @endforeach
         </tbody>
      </table>
      <pagebreak sheet-size='A4'>
   @endforeach
   </body>
</html>