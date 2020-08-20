# Creating a scenario

There's an elaborate architecture description in Russian at [Habrahabr](https://habr.com/ru/company/postuf/blog/486322/).

## Table of contents

* [1. Library organization](#1-library-organization)
  * [1.1. Architecture guidelines](#11-architecture-guidelines)
* [2. Implementing a scenario](#2-implementing-a-scenario)
  * [2.1. Adding necessary nodes](#21-adding-necessary-nodes)
  * [2.2. Implementing client calls](#22-implementing-client-calls)
  * [2.3. Implementing client calls](#23-implementing-the-scenario)
  * [2.4. Testing the scenario](#24-testing-the-scenario)

## 1. Library organization

The library has several architectural layers:

* [protocol layer](https://github.com/Postuf/telegram-osint-lib/tree/master/src/TLMessage/TLMessage) – [MTProto](https://core.telegram.org/mtproto/TL) nodes encoding/decoding
* [client layer](https://github.com/Postuf/telegram-osint-lib/tree/master/src/Client) – API calls
* [scenario layer](https://github.com/Postuf/telegram-osint-lib/tree/master/src/Scenario) – end-to-end scenarios solving some problems
* [application layer](https://github.com/Postuf/telegram-osint-lib/tree/master/examples) – we have some examples of using and combining scenarios.

### 1.1. Architecture guidelines

We aim at the following principles while designing the library:

* *fail fast* – although the protocol documentation is open, the protocol itself is proprietary, so the library should properly react to unexpected API changes
* *conformity* – the library behaves as close to official clients as possible
* *testability* – the code should be testable (preferably using end-to-end tests), have low coupling

## 2. Implementing a scenario

Suppose, you have a purpose your application should do, for example, would like to get featured stickers.

### 2.1 Adding necessary nodes

First of all, you need to implement all TL nodes needed to solve your problem. There are two types of nodes in library:

* [client nodes](https://github.com/Postuf/telegram-osint-lib/blob/master/src/TLMessage/TLMessage/TLClientMessage.php), those we send to Telegram server
* [server nodes](https://github.com/Postuf/telegram-osint-lib/blob/master/src/TLMessage/TLMessage/TLServerMessage.php), those we receive from Telegram server

You can use [official API doc](https://core.telegram.org/) as a reference to implement the nodes.

### 2.2. Implementing client calls

The next step is adding the calls to a client.
The best way would be to extend some base Client (like InfoClient) with your methods.
For example, https://github.com/Postuf/telegram-osint-lib/blob/c4a6fbdd35c2f56f3de3f03d55f5237125459cf0/src/Client/InfoObtainingClient/StickerClient.php – here we added methods to perform `get_featured_stickers` and `get_sticker_set` calls.
Implementing an interface with your calls is considered architecturally a good practice, for example, we can add some caching upon your client if needed.

### 2.3. Implementing the scenario

The scenario is a piece of application logic you can use to solve some task.
The simplest scenario has some operation it performs (getting featured stickers in our case) and a callback to be called upon completion.
See: https://github.com/Postuf/telegram-osint-lib/blob/c4a6fbdd35c2f56f3de3f03d55f5237125459cf0/src/Scenario/FeaturedStickerSetScenario.php

Scenarios can call other scenarios to achieve their goal, for example: https://github.com/Postuf/telegram-osint-lib/blob/c4a6fbdd35c2f56f3de3f03d55f5237125459cf0/src/Scenario/SearchUserScenario.php#L89

### 2.4 Testing the scenario

We use a concept of traces to test scenarios. A trace is a sequence of responses from server we receive. To store a sequence (from a real communication session), please use `TracingBasicClientImpl`: https://github.com/Postuf/telegram-osint-lib/blob/c4a6fbdd35c2f56f3de3f03d55f5237125459cf0/src/Scenario/SearchUserScenario.php#L89

A text file would be generated, next, you can use it in your test: https://github.com/Postuf/telegram-osint-lib/blob/c4a6fbdd35c2f56f3de3f03d55f5237125459cf0/tests/Integration/Scenario/GeoSearchTest.php

Before being used in your test, file needs to be converted to a readable format, there is a tool for that:

```
php traceConverter.php -i ../examples/26836dce4e3e1489348b83c536f742ce.txt
```

The resulting file would be a JSON containing a sequence of nodes received from server in human-readable format.

The file may need some filtering to exclude unnecessary nodes; in whole such test indicates that your scenario achieves some desired result in a real session with Telegram server.
