import React from 'react';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import {
  ToggleControl,
  TextareaControl,
  BaseControl,
  PanelBody,
} from '@wordpress/components';
import Select from 'react-select';

function NotificationItem({
  title,
  description,
  keyName,
  settings,
  setOption,
}) {
  const replacements = settings.replacements
    .map((item) => `<code>{${item}}</code>`)
    .join(', ');

  const roles = [
    {
      label: __('— Select User Role —', 'texty'),
      value: '',
    },
    { label: 'Administrator', value: 'administrator' },
    { label: 'Editor', value: 'editor' },
    { label: 'Author', value: 'author' },
  ];

  return (
    <PanelBody
      initialOpen={false}
      title={
        <>
          <span>{title}</span>
          <span
            className={classNames('label', {
              active: settings.enabled === true,
            })}
          >
            {settings.enabled ? 'active' : 'inactive'}
          </span>
        </>
      }
    >
      <ToggleControl
        label={__('Enable', 'texty')}
        help={description}
        checked={settings.enabled}
        onChange={(isChecked) => setOption(keyName, 'enabled', isChecked)}
      />

      {settings.enabled && (
        <>
          {settings.type === 'role' && (
            <BaseControl
              label={__('Recipients')}
              help={
                settings.type === 'role'
                  ? __('Select one or multiple user roles.')
                  : ''
              }
            >
              <Select
                isMulti={settings.type === 'role'}
                value={roles.filter((item) =>
                  settings.recipients.includes(item.value)
                )}
                options={roles}
                onChange={(options) => {
                  if (options !== null) {
                    setOption(
                      keyName,
                      'recipients',
                      options.map((item) => item.value)
                    );
                  } else {
                    setOption(keyName, 'recipients', []);
                  }
                }}
              />
            </BaseControl>
          )}

          <TextareaControl
            label={__('Message', 'texty')}
            help={
              <span
                className="help"
                dangerouslySetInnerHTML={{
                  __html:
                    __('You may use these variables: ', 'texty') + replacements,
                }}
              ></span>
            }
            value={settings.message}
            onChange={(text) => setOption(keyName, 'message', text)}
          />
        </>
      )}
    </PanelBody>
  );
}

export default NotificationItem;