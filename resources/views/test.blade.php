<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>抓取</title>
    <style>
        .progress {
            height: 20px;
            background: #ebebeb;
            border-left: 1px solid transparent;
            border-right: 1px solid transparent;
            border-radius: 10px;
        }
        .progress > span {
            position: relative;
            float: left;
            margin: 0 -1px;
            min-width: 30px;
            height: 18px;
            line-height: 16px;
            text-align: right;
            background: #cccccc;
            border: 1px solid;
            border-color: #bfbfbf #b3b3b3 #9e9e9e;
            border-radius: 10px;
            background-image: -webkit-linear-gradient(top, #f0f0f0 0%, #dbdbdb 70%, #cccccc 100%);
            background-image: -moz-linear-gradient(top, #f0f0f0 0%, #dbdbdb 70%, #cccccc 100%);
            background-image: -o-linear-gradient(top, #f0f0f0 0%, #dbdbdb 70%, #cccccc 100%);
            background-image: linear-gradient(to bottom, #f0f0f0 0%, #dbdbdb 70%, #cccccc 100%);
            -webkit-box-shadow: inset 0 1px rgba(255, 255, 255, 0.3), 0 1px 2px rgba(0, 0, 0, 0.2);
            box-shadow: inset 0 1px rgba(255, 255, 255, 0.3), 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        .progress > span > span {
            padding: 0 8px;
            font-size: 11px;
            font-weight: bold;
            color: #404040;
            color: rgba(0, 0, 0, 0.7);
            text-shadow: 0 1px rgba(255, 255, 255, 0.4);
        }
        .progress > span:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1;
            height: 18px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div style="width:50%;margin:0 auto;">
        <button id="start">开始抓取</button>
        <input id="url" type="text" style="width:300px;height:20px;" placeholder="默认抓取海口市二手房源，可以输入url进行更改"/>
        <div class="progress" style="margin:30px 0 0 0">
            <span style="width: 0%;" id="progress"><span>0%</span></span>
        </div>
    </div>
    <script>
        window.onload = func => {
            const start = document.getElementById('start')
            const progress = document.getElementById('progress')
            start.onclick = func => {
                console.log(url.value);
                start.innerHTML = '正在抓取...'
                start.disabled = 'disabled'
                for(let i = 1;i <= 100; i++){
                    ajax({
                        url : "http://test.test/api/getOnePageHouseMultiThread",//getOnePageHouse  getOnePageHouseMultiThread
                        type : "GET",
                        data: { page: i ,url: document.getElementById('url').value},
                        async : true,
                        success : function(data){
                            if(data.code == 0 && i == 100){
                                start.innerHTML = '开始抓取'
                                start.disabled = ''
                            }
                            if(data.code == 0){
                                progress.style.width = parseInt(progress.style.width) + 1 + '%'
                                progress.getElementsByTagName('span')[0].innerHTML = progress.style.width
                            }
                        }
                    })
                }
            }
        }
        function ajax(options){
            let xhr = null
            const params = formsParams(options.data)

            if(window.XMLHttpRequest){
                xhr = new XMLHttpRequest()
            } else {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }

            if(options.type == "GET"){
                xhr.open(options.type,options.url + "?"+ params,options.async);
                xhr.send(null)
            } else if(options.type == "POST"){
                xhr.open(options.type,options.url,options.async);
                xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
                xhr.send(params);
            }

            xhr.onreadystatechange = func => {
                if(xhr.readyState == 4 && xhr.status == 200){
                    options.success(JSON.parse(xhr.responseText));
                }
            }
            function formsParams(data){
                let arr = [];
                for(let prop in data){
                    arr.push(prop + "=" + data[prop]);
                }
                return arr.join("&");
            }

        }
    </script>
</body>
</html>