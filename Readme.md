#  XE-LIFO Captcha(Powered by NAVER™ OpenAPI)

> XE 에서 사용할 수 있도록 제작된 캡차 애드온 입니다.

![GitHub](https://img.shields.io/github/license/LIFOsitory/xe-Naver.openapi-captcha.svg?style=flat-square)
![GitHub release](https://img.shields.io/github/release/LIFOsitory/xe-Naver.openapi-captcha.svg?style=flat-square)

### XE

XpressEngine(XE)은 누구나 쉽고 편하고 자유롭게 콘텐츠를 발행을 할 수 있도록 하기 위한 CMS(Content Management System)입니다. 

자세한 내용은 [XE](https://github.com/xpressengine/xe-core)에서 확인하세요.

#### Based on captcha addon

XE에 내장된 캡차를 기반으로 제작되었습니다.

### NAVER™ OPENAPI IMAGE CAPTCHA

네이버 서비스에서 사용하고 있는 이미지 캡차 기능을 고객의 서비스에 활용하여 사람과 컴퓨터를 판별해 어뷰징을 막을 수 있습니다. 

자세한 내용은 [네이버™ 개발자센터](https://developers.naver.com/products/captcha/)에서 확인하세요.

## 💾 Install

- 릴리즈에서 최신 버전의 소스를 다운로드 합니다.
- 압축을 풀고 폴더 이름을 **naver_openapi_captcha** 로 변경합니다.
- XE의 addons 폴더 안으로 이동시킵니다.

### API 사용 신청

- NAVER OpenAPI를 이용하므로 API 이용신청이 필요합니다.
- [여기](https://developers.naver.com/apps/#/register?defaultScope=captcha)에서 애플리케이션을 등록합니다.

## 🔨 Usage

- 관리자 페이지에서 설치된 에드온 목록을 확인합니다.
- NAVER™ OpenAPI Captcha 애드온을 설정합니다.
- NAVER™ 개발자 센터에서 클라이언트 ID와 Secret을 받아 입력합니다.
- 테마 및 기타 설정을 완료한 뒤 저장합니다.
- PC 또는 Mobile에 체크합니다.
 
❗️ 다른 캡차와 같이 사용할 경우 충돌할 수 있습니다. 

### Limit

- 처리한도(무료) : 1,000건/일
- 제휴신청은 API를 일 호출 허용량 이상으로 사업적으로 사용하기 위해 API 사용량, API 사용처, API 활용목적에 대해 검토를 받는 절차이며 API 사용처, 활용 목적에 따라 제휴승인이 거절될 수 있습니다.
- [네이버™ 클라우드 플랫폼](https://www.ncloud.com/product/applicationService/captcha)에서 제휴 신청할 수 있습니다.

❗️❗️ 처리한도 이상으로 사용시 캡차 기능은 작동하지 않습니다.

### Language

- English
- 한국어

## Skin
- 밝은 테마
- 어두운 테마

#### Based on Bootstrap
기본 제공 스킨은 Bootstrap v4.2.1 을 기반으로 제작되었습니다.

❗️ 클래스명 충돌에 유의하세요.

### Add Skin
- skin 폴더에 원하는 스킨 이름의 폴더를 생성합니다.
- view.html, view.css를 생성하여 원하는 스킨을 디자인 합니다.
- conf 폴더의 info.xml에서 skin 항목에 생성한 스킨을 추가합니다.
- 관리자 페이지에서 해당 스킨을 선택하여 사용합니다.

## 📜 License

This software is licensed under the [LGPL-3.0](https://github.com/LIFOsitory/xe-Naver.openapi-captcha/blob/master/LICENSE) © [LIFOsitory](https://github.com/LIFOsitory).