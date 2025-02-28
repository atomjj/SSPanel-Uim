<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Notification;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client\ClientExceptionInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;
use function date;
use function time;

/**
 * Ip Model
 */
final class LoginIp extends Model
{
    protected $connection = 'default';
    protected $table = 'login_ip';

    /**
     * 登录用户
     */
    public function user(): ?User
    {
        return User::find($this->userid);
    }

    /**
     * 登录用户名
     */
    public function userName(): string
    {
        return $this->user() === null ? '用户不存在' : $this->user()->user_name;
    }

    /**
     * 登录成功与否
     */
    public function type(): string
    {
        return $this->type === 0 ? '成功' : '失败';
    }

    /**
     * 记录登录 IP
     *
     * @param string $ip IP
     * @param int $type 1 = failed, 0 = success
     * @param int $user_id User ID
     *
     * @return void
     *
     * @throws GuzzleException
     * @throws ClientExceptionInterface
     * @throws TelegramSDKException
     */
    public function collectLoginIP(string $ip, int $type = 0, int $user_id = 0): void
    {
        if (Setting::obtain('login_log')) {
            $this->ip = $ip;
            $this->userid = $user_id;
            $this->datetime = time();
            $this->type = $type;

            if (Setting::obtain('notify_new_login') &&
                $user_id !== 0 &&
                LoginIp::where('userid', $user_id)->where('ip', $this->ip)->count() === 0
            ) {
                try {
                    Notification::notifyUser(
                        User::where('id', $user_id)->first(),
                        $_ENV['appName'] . '-新登录通知',
                        '你的账号于 ' . date('Y-m-d H:i:s') . ' 通过 ' . $this->ip . ' 地址登录了用户面板',
                    );
                } catch (GuzzleException|ClientExceptionInterface|TelegramSDKException $e) {
                    echo $e->getMessage();
                }
            }

            $this->save();
        }
    }
}
