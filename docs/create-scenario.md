# Creating a scenario

There's an elaborate architecture description in Russian at [Habrahabr](https://habr.com/ru/company/postuf/blog/486322/).

## Table of contents

* [1. Library organization](#1-library-organization)
  * [1.1. Architecture guidelines](#11-architecture-guidelines)
* [2. Implementing a scenario](#2-implementing-a-scenario)
  * [2.1. Adding necessary nodes](#21-adding-necessary-nodes)
  * [](#22-implementing-client-calls)

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

...
