<?php
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php
$isLoginPage = Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'login';
$identity = Yii::$app->user->identity;
$canManageChats = $identity instanceof \app\models\User && $identity->canManageClients();
$chatStorageKey = 'ecoChatSession_guest';
if (!Yii::$app->user->isGuest && $identity instanceof \app\models\User) {
    $clientKey = $identity->getDefaultClientId();
    $chatStorageKey = sprintf('ecoChatSession_user_%d', $identity->id);
    if ($clientKey) {
        $chatStorageKey .= sprintf('_client_%d', $clientKey);
    }
}

$chatConfig = [
    'session' => Yii::$app->request->baseUrl . '/chat/session',
    'base' => Yii::$app->request->baseUrl . '/chat',
    'storageKey' => $chatStorageKey,
];
?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Eco Manager',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-expand-lg navbar-dark bg-dark',
        ],
    ]);
    $identity = Yii::$app->user->identity;
    $menuItems = [];
    if (!Yii::$app->user->isGuest) {
        if ($identity instanceof \app\models\User && $identity->canManageClients()) {
            $menuItems[] = ['label' => 'Добавить клиента', 'url' => ['/client/onboard']];
            $menuItems[] = '<li class="nav-item position-relative">
                <a class="nav-link chat-nav-link" href="' . Html::encode(Url::to(['/chat/inbox'])) . '" data-chat-nav-link>
                    Чаты
                    <span class="chat-nav-indicator" data-chat-nav-indicator>Новые</span>
                    <span class="chat-nav-indicator chat-nav-indicator--mine" data-chat-nav-mine>Мои</span>
                </a>
            </li>';
        }
        if ($identity->role === \app\models\User::ROLE_ADMIN) {
            $menuItems[] = ['label' => 'Пользователи', 'url' => ['/user/index']];
        }
        $menuItems[] = '<li class="nav-item ms-lg-3">' .
            Html::tag('span', Html::encode($identity->username) . ' · ' . Html::encode($identity->getRoleLabel()), [
                'class' => 'navbar-text text-white-50',
            ]) .
            '</li>';
        $menuItems[] = '<li class="nav-item">' .
            Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline chat-logout-form', 'data-chat-logout' => '1']) .
            Html::submitButton('Выход', ['class' => 'btn btn-sm btn-outline-light ms-lg-3']) .
            Html::endForm() .
            '</li>';
    } else {
        $menuItems[] = ['label' => 'Войти', 'url' => ['/site/login']];
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav ms-auto align-items-center gap-2'],
        'items' => $menuItems,
        'encodeLabels' => false,
    ]);
    NavBar::end();
    ?>

    <main class="main-content<?= $isLoginPage ? ' main-content--centered' : '' ?>">
        <div class="container<?= $isLoginPage ? ' login-container' : ' main-container' ?>">
            <?= $content ?>
        </div>
    </main>
</div>

<footer class="footer py-3 bg-light">
    <div class="container">
        <p class="text-muted mb-0">&copy; Eco Manager <?= date('Y') ?></p>
    </div>
</footer>

<?php if (!Yii::$app->user->isGuest && !$canManageChats): ?>
    <div id="chatWidget" class="chat-widget" data-config='<?= Json::htmlEncode($chatConfig) ?>'>
        <div class="chat-panel" data-chat-panel aria-hidden="true">
            <div class="chat-panel__header">
                <div>
                    <div class="chat-panel__title">Служба поддержки</div>
                    <div class="chat-panel__subtitle">Ответим в рабочее время ≤ 30 минут</div>
                </div>
                <button type="button" class="chat-panel__close" data-chat-close aria-label="Закрыть">&times;</button>
            </div>
            <div class="chat-panel__body">
                <div class="chat-panel__messages" data-chat-messages></div>
                <div class="chat-panel__empty text-muted small" data-chat-empty>
                    Сообщений пока нет. Расскажите, что вас интересует — мы подключим оператора.
                </div>
                <form id="chatForm" class="chat-form">
                    <div data-chat-details>
                        <div class="mb-3">
                            <label for="chatName" class="form-label">Как к вам обращаться</label>
                            <input type="text" class="form-control" id="chatName" name="name" placeholder="Например, Анна">
                        </div>
                        <div class="mb-3">
                            <label for="chatContact" class="form-label">Email или Telegram</label>
                            <input type="text" class="form-control" id="chatContact" name="contact" placeholder="@username или email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="chatMessage" class="form-label">Сообщение</label>
                        <textarea class="form-control" id="chatMessage" name="message" rows="3" placeholder="Чем мы можем помочь?" required></textarea>
                    </div>
                    <div class="mb-3" data-chat-phone-group>
                        <label for="chatPhone" class="form-label">Телефон для обратного звонка (необязательно)</label>
                        <input type="tel" class="form-control" id="chatPhone" name="phone" placeholder="+7 (___) ___-__-__">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        Отправить
                    </button>
                </form>
                <div class="chat-panel__status" data-chat-status></div>
                <p class="chat-panel__hint text-muted">
                    Сообщения сохраняются в истории. Вы можете вернуться позже — чат останется доступным на этом устройстве.
                </p>
            </div>
        </div>
        <div class="chat-trigger-wrapper">
            <span class="chat-tip" data-chat-tip>Новое сообщение</span>
            <button type="button" class="chat-trigger btn btn-primary" data-chat-trigger>
                Написать нам
            </button>
        </div>
    </div>
<?php endif; ?>
<?php
$chatWidgetJs = <<<'JS'
(function () {
const widget = document.getElementById('chatWidget');
if (!widget) {
    return;
}

const cfg = JSON.parse(widget.dataset.config || '{}');
const STORAGE_KEY = cfg.storageKey || 'ecoChatSessionId';
let sessionId = localStorage.getItem(STORAGE_KEY);
let panelOpen = false;
let pollTimer = null;
let lastMessageId = 0;
const trigger = widget.querySelector('[data-chat-trigger]');
const panel = widget.querySelector('[data-chat-panel]');
const closeBtn = widget.querySelector('[data-chat-close]');
const statusNode = widget.querySelector('[data-chat-status]');
const form = widget.querySelector('#chatForm');
const detailsBlock = widget.querySelector('[data-chat-details]');
const phoneBlock = widget.querySelector('[data-chat-phone-group]');
const emptyState = widget.querySelector('[data-chat-empty]');
const messagesBox = widget.querySelector('[data-chat-messages]');
const tip = widget.querySelector('[data-chat-tip]');
const navIndicator = document.querySelector('[data-chat-nav-indicator]');
const navMyIndicator = document.querySelector('[data-chat-nav-mine]');
const navLink = document.querySelector('[data-chat-nav-link]');
const logoutForms = document.querySelectorAll('[data-chat-logout]');
const LAST_SEEN_PREFIX = 'ecoChatLastSeen';
const nameField = form?.querySelector('[name="name"]');
const contactField = form?.querySelector('[name="contact"]');
const messageField = form?.querySelector('[name="message"]');
const phoneField = form?.querySelector('[name="phone"]');
let submitting = false;
let lastSeenId = 0;
let navLastTs = localStorage.getItem('ecoChatNavTs') || '';
let navPollTimer = null;
const getLastSeenKey = () => (sessionId ? `${LAST_SEEN_PREFIX}_${sessionId}` : LAST_SEEN_PREFIX);
const loadLastSeen = () => {
    if (!sessionId) {
        lastSeenId = 0;
        return;
    }
    lastSeenId = parseInt(localStorage.getItem(getLastSeenKey()) || '0', 10);
};
const saveLastSeen = () => {
    if (!sessionId) {
        return;
    }
    localStorage.setItem(getLastSeenKey(), String(lastSeenId));
};
const clearChatStorage = () => {
    localStorage.removeItem(STORAGE_KEY);
    if (STORAGE_KEY !== 'ecoChatSessionId') {
        localStorage.removeItem('ecoChatSessionId');
    }
    sessionId = null;
    lastSeenId = 0;
};

const escapeHtml = (value) => value.replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
}[char] || char));

const formatTime = (value) => {
    try {
        return new Intl.DateTimeFormat('ru-RU', {
            hour: '2-digit',
            minute: '2-digit',
            day: '2-digit',
            month: 'short',
        }).format(new Date(value));
    } catch (e) {
        return value;
    }
};

const setStatus = (text, isError = false) => {
    if (!statusNode) {
        return;
    }
    statusNode.textContent = text;
    statusNode.classList.toggle('is-error', isError);
    statusNode.classList.toggle('is-success', !isError && !!text);
};

const setTip = (visible) => {
    tip?.classList.toggle('is-visible', visible);
};

const setNavIndicator = (visible) => {
    navIndicator?.classList.toggle('is-visible', visible);
};

const setNavMyIndicator = (visible) => {
    navMyIndicator?.classList.toggle('is-visible', visible);
};

const togglePanel = (show) => {
    if (!panel) {
        return;
    }
    panelOpen = show;
    panel.classList.toggle('is-open', show);
    panel.setAttribute('aria-hidden', show ? 'false' : 'true');
    widget.classList.toggle('is-open', show);
    if (show) {
        setTip(false);
        updateFormState();
        if (sessionId) {
            loadMessages(false);
        }
        ensurePolling();
        messageField?.focus();
    } else {
        ensurePolling();
    }
};

const updateFormState = () => {
    const hasSession = Boolean(sessionId);
    detailsBlock?.classList.toggle('d-none', hasSession);
    phoneBlock?.classList.toggle('d-none', hasSession);
    if (nameField) {
        nameField.required = !hasSession;
    }
    if (contactField) {
        contactField.required = !hasSession;
    }
};

const renderMessages = (messages, append = false) => {
    if (!messagesBox) {
        return;
    }
    if (!append) {
        messagesBox.innerHTML = '';
    }
    messages.forEach((message) => {
        const bubble = document.createElement('div');
        const isOperator = message.sender_type === 'operator';
        bubble.className = `chat-bubble ${isOperator ? 'chat-bubble--operator' : 'chat-bubble--client'}`;
        bubble.innerHTML = `
            <div class="chat-bubble__body">${escapeHtml(message.body)}</div>
            <div class="chat-bubble__time">
                ${isOperator ? 'Оператор' : 'Вы'}
                &middot;
                ${formatTime(message.created_at)}
            </div>
        `;
        messagesBox.appendChild(bubble);
    });

    emptyState?.classList.toggle('d-none', messagesBox.children.length > 0);
    messagesBox.scrollTop = messagesBox.scrollHeight;
    if (panelOpen) {
        lastSeenId = lastMessageId;
        saveLastSeen();
        setTip(false);
    } else if (lastMessageId > lastSeenId) {
        setTip(true);
    }
};

const ensurePolling = () => {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
    if (sessionId) {
        pollTimer = setInterval(() => loadMessages(true), 5000);
    }
};

const handleSessionResponse = (data) => {
    if (data?.session?.id) {
        sessionId = data.session.id;
    } else if (data?.id) {
        sessionId = data.id;
    }
    if (sessionId) {
        localStorage.setItem(STORAGE_KEY, sessionId);
        lastSeenId = 0;
        loadLastSeen();
        updateFormState();
        ensurePolling();
    }
};

const loadMessages = async (append = false) => {
    if (!sessionId || !cfg.base) {
        return;
    }

    try {
        const url = `${cfg.base}/${sessionId}?since_id=${append ? lastMessageId : 0}`;
        const response = await fetch(url);
        if (response.status === 404) {
            stopStream();
            clearChatStorage();
            updateFormState();
            messagesBox.innerHTML = '';
            emptyState?.classList.remove('d-none');
            setStatus('Сессия устарела, начните новый чат', true);
            return;
        }
        if (!response.ok) {
            throw new Error('Не удалось получить историю');
        }
        const data = await response.json();
        const messages = Array.isArray(data?.messages) ? data.messages : [];
        if (!append) {
            lastMessageId = 0;
        }
        if (messages.length) {
            messages.forEach((msg) => {
                lastMessageId = Math.max(lastMessageId, msg.id || 0);
            });
            renderMessages(messages, append);
        } else if (!append && messagesBox) {
            messagesBox.innerHTML = '';
            emptyState?.classList.remove('d-none');
        }
    } catch (error) {
        console.error('[chat] history', error);
    }
};

trigger?.addEventListener('click', () => togglePanel(true));
closeBtn?.addEventListener('click', () => togglePanel(false));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        togglePanel(false);
    }
});

form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (submitting) {
        return;
    }

    const name = (nameField?.value || '').trim();
    const contact = (contactField?.value || '').trim();
    const body = (messageField?.value || '').trim();
    const phone = (phoneField?.value || '').trim();

    if (!sessionId && (!name || !contact)) {
        setStatus('Заполните имя и контакт, чтобы мы могли ответить', true);
        return;
    }

    if (!body) {
        setStatus('Введите сообщение', true);
        return;
    }

    setStatus('');
    submitting = true;
    form.classList.add('is-loading');

    try {
        if (!sessionId) {
            const createResponse = await fetch(cfg.session, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name,
                    external_contact: contact,
                    initial_message: body,
                }),
            });
            const data = await createResponse.json();
            if (!createResponse.ok) {
                throw new Error(data?.errors ? 'Проверьте заполненные поля' : 'Не удалось создать чат');
            }
            handleSessionResponse(data);
            messageField.value = '';
            if (phone) {
                await fetch(`${cfg.base}/${sessionId}/callback`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ phone }),
                });
                phoneField.value = '';
            }
            await loadMessages(false);
            setStatus('Мы получили сообщение и подключим оператора', false);
        } else {
            const response = await fetch(`${cfg.base}/${sessionId}/message`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    sender_type: 'client',
                    body,
                }),
            });
            if (response.status === 404) {
                stopStream();
                clearChatStorage();
                updateFormState();
                throw new Error('Сессия устарела, начните новый чат');
            }
            if (!response.ok) {
                throw new Error('Не удалось отправить сообщение');
            }
            messageField.value = '';
            await loadMessages(true);
            setStatus('Сообщение отправлено', false);
        }
    } catch (error) {
        setStatus(error.message || 'Не удалось отправить сообщение', true);
        console.error('[chat] send', error);
    } finally {
        submitting = false;
        form.classList.remove('is-loading');
    }
});

if (sessionId) {
    loadLastSeen();
    updateFormState();
    loadMessages(false);
    ensurePolling();
}

const startNavPolling = () => {
    if (!navIndicator || !cfg.base) {
        return;
    }
    const pollNav = async () => {
        try {
            const url = `${cfg.base}/alerts${navLastTs ? `?since=${encodeURIComponent(navLastTs)}` : ''}`;
            const response = await fetch(url);
            if (response.status === 403) {
                clearInterval(navPollTimer);
                setNavIndicator(false);
                setNavMyIndicator(false);
                return;
            }
            if (!response.ok) {
                throw new Error('alerts request failed');
            }
            const data = await response.json();
            if (data?.latest_new) {
                navLastTs = data.latest_new;
                localStorage.setItem('ecoChatNavTs', navLastTs);
            }
            setNavIndicator(Boolean(data?.has_new_dialogs));
            setNavMyIndicator(Boolean(data?.has_my_updates));
        } catch (error) {
            console.error('[chat] alerts', error);
        }
    };

    pollNav();
    navPollTimer = setInterval(pollNav, 5000);
};

startNavPolling();
navLink?.addEventListener('click', () => {
    setNavIndicator(false);
    setNavMyIndicator(false);
});
logoutForms.forEach((form) => {
    form.addEventListener('submit', () => {
        clearChatStorage();
    });
});
})();
JS;
$this->registerJs($chatWidgetJs);
?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
