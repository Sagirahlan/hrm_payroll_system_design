<?php

// Base64 encoded logo
$logo_base64 = "iVBORw0KGgoAAAANSUhEUgAAAWEAAADGCAYAAAAxfSzHAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAP+lSURBVHhe7J0HfFzF9f//znYVFfeubnkVd1yEe8HdGDBgU0wLLQQTQgkhEAIhQHohlIT0n9BLwDRjMGBj4953qVqyLEvW9r5z7pk7d3ef1a7WtlGc53ndL2O9eu/ud+8985nvzMwoXkmCIAiC8LeFogiCIAj/QCQJCIIgCH9jJAkIgiAIf2MkCQiCIPyNkSQgCIIg/I2RJCAIgiD8jZEkIAj/AKSl7/Nm99y3L/yG7/x19AY/w56XJCAIwt8NQfz70i/w9a4Xf2H6d/2i9nNEkoAgCH8D+LNrg4b0uzD7fyGvt/9K+je/bSQJCMLfBE789kW/pv+Tb/pv96/8fZEkIAh/K3x+YYnN/7kkX/jfKKSbK7+UQiTxd0SSgCD87VChLy1Xby+1QfpIl+0HX39u/z1+/X9LSH+bJCAIf08kMfw9kQJcX3L+H/uVvxmSBAThb4KP+P0X+F/z8r+k31HoB+xd/48kAUH426BCXlpd+z/+a/0r/0FISTi8uNv8jyRx//9KE/B/uSAnSUAQvmoY+ZFHf0t+3/zLPhIlfPlImFDe+z/+e311SP/0RPBlb/q/1Mf+DUkSEISvEBT5P6pP+H/2v/zZKBJYFLnUUPK1rv84pDn/3m9L+pP2/O8kAUH4r0WCvKyJdKN4v99NP/z3/vr/BEnEy+YQ5C/u+t9XAZAE8Xe/vb9TAU6SgCD8WcivrzOI/z3w+C/5uS/p//uXvKX/R8rgH/t9SJJY+ge+kL/zk/5JBbC/U5/wf+j8fwMkCQjCXwhBGH+c1Y/xd+3P+af5H+vH64+/rv94X9GIV9mRtQ+x/yu/Vbn9N/2+pD/1GZKxlwRJBtL//z+RJCAI//1I0iTu/yQRlP7fXxQPCZ2i/qNXvNQ//+e/72uC+p/6rX/yPv41JP/d60v+i74m/Q1JAoLwl0GQwBOaWMo/69vSj/8+J5i/lP8fFMHSn/+8H+/r//mr/07lAv+T+rJ/hAn4vyMJCAIpEJ+A/zt98N/s3/hH/dVfE8i/UPflf6pY/tv+C/8P/0VpSiT8Z3xRX/bX/4FJElD+D/xW/3Wg/PP+9L/mh4iqSP4TJPCP/Lb+r0EQhb8X/6z/m//mv/+7lv6JJCAIwt8cSQ78B76L/xcBAP8/B/b4FkFWvQkAAAAASUVORK5CYII=";

// Common header HTML
$header_html = '<img src="data:image/png;base64,' . $logo_base64 . '" class="logo" alt="KSWB Logo" />
            <div class="org-name">KATSINA STATE WATER BOARD</div>';

// Common watermark CSS
$watermark_css = '        .page::before {
            content: "KATSINA STATE WATER BOARD";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(30, 64, 175, 0.03);
            font-weight: bold;
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
        }';

// Common logo CSS
$logo_css = '        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 8px;
        }

        .org-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }';

echo "Branding components ready for all reports\n";
echo "Logo Base64 length: " . strlen($logo_base64) . " characters\n";
