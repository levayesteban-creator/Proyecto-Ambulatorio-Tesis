<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecimiento de Contraseña</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f8;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f8;padding:20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    <!-- HEADER -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:20px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:22px;font-weight:bold;">Consultorio Popular Tipo III</h1>
                            <h2 style="color:#f0f0f0;margin:5px 0 0;font-size:16px;">"El Chaparro de Guanta"</h2>
                        </td>
                    </tr>

                    <!-- CONTENIDO -->
                    <tr>
                        <td style="padding:30px;line-height:1.6;color:#333333;">
                            <h2 style="color:#666;font-size:20px;margin:0 0 15px;">Recuperación de Contraseña</h2>
                            <p style="font-size:15px;color:#555;margin:0 0 10px;">
                                Hola {{ $notifiable->name }},
                            </p>
                            <p style="font-size:15px;color:#555;margin:0 0 10px;">
                                Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en el sistema de gestión médica del Consultorio "El Chaparro de Guanta".
                            </p>
                            <p style="font-size:15px;color:#555;margin:0 0 10px;">
                                Si solicitaste este cambio, haz clic en el siguiente botón para establecer una nueva contraseña:
                            </p>

                            <div style="text-align:center;margin:25px 0;">
                                <a href="{{ $url }}" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#ffffff;padding:12px 30px;text-decoration:none;border-radius:5px;font-weight:bold;font-size:15px;display:inline-block;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                                    Restablecer Contraseña
                                </a>
                            </div>

                            <p style="font-size:14px;color:#888;margin:0 0 10px;">
                                Este enlace expirará en {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutos.
                            </p>
                            <p style="font-size:14px;color:#888;margin:0;">
                                Si no solicitaste este cambio, por favor ignora este correo y tu contraseña permanecerá sin cambios.
                            </p>
                        </td>
                    </tr>

                    <!-- INFORMACION DE SEGURIDAD -->
                    <tr>
                        <td style="padding:0 30px 20px;">
                            <div style="padding:12px;background-color:#f9f9f9;border-left:4px solid #667eea;border-radius:4px;font-size:13px;color:#666;">
                                <strong>🔒 Información de Seguridad:</strong> Este correo fue enviado desde el sistema de gestión médica del Consultorio "El Chaparro de Guanta". Si tienes preguntas sobre la seguridad de tu cuenta, contacta al administrador del sistema.
                            </div>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="padding:20px 30px;border-top:1px solid #e0e0e0;text-align:center;">
                            <p style="font-size:13px;color:#888;margin:3px 0;">Consultorio Popular Tipo III "El Chaparro de Guanta"</p>
                            <p style="font-size:12px;color:#999;margin:3px 0;">Sistema de Gestión de Historias Clínicas</p>
                            <p style="font-size:11px;color:#aaa;margin:3px 0;">© {{ date('Y') }} - Todos los derechos reservados</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
